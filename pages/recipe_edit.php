<?php
// Edition d'une recette
require_once '../includes/db.php';
require_once '../includes/functions.php';

$basePath = '../';

// Verifier l'authentification
if (!isLoggedIn()) {
    redirect('login.php', 'Connectez-vous pour modifier une recette', 'error');
}

// Verifier l'ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('index.php', 'Recette non trouvee', 'error');
}

$recipeId = (int)$_GET['id'];

// Recuperer la recette
$stmt = $pdo->prepare('SELECT * FROM recipe WHERE id = ?');
$stmt->execute([$recipeId]);
$recipe = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$recipe) {
    redirect('index.php', 'Recette non trouvee', 'error');
}

// Verifier que l'utilisateur est le proprietaire ou admin
if ($recipe['user_id'] != $_SESSION['user_id'] && !isAdmin()) {
    redirect('index.php', 'Vous n\'avez pas la permission de modifier cette recette', 'error');
}

$pageTitle = 'Modifier : ' . $recipe['title'];

// Recuperer tous les tags
$allTags = $pdo->query('SELECT * FROM tag ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);

// Recuperer les donnees actuelles
$currentIngredients = getRecipeIngredients($pdo, $recipeId);
$currentSteps = getRecipeSteps($pdo, $recipeId);
$currentTags = array_column(getRecipeTags($pdo, $recipeId), 'id');

$errors = [];
$formData = [
    'title' => $recipe['title'],
    'description' => $recipe['description'],
    'status' => $recipe['status'],
    'ingredients' => !empty($currentIngredients) ? $currentIngredients : [['name' => '', 'quantity' => '']],
    'steps' => !empty($currentSteps) ? array_column($currentSteps, 'content') : [''],
    'tags' => $currentTags
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recuperer les donnees
    $formData['title'] = trim($_POST['title'] ?? '');
    $formData['description'] = trim($_POST['description'] ?? '');
    $formData['status'] = $_POST['status'] ?? 'draft';
    $formData['ingredients'] = $_POST['ingredients'] ?? [];
    $formData['steps'] = $_POST['steps'] ?? [];
    $formData['tags'] = $_POST['tags'] ?? [];
    
    // Validation
    if (empty($formData['title'])) {
        $errors[] = 'Le titre est obligatoire';
    }
    
    if (strlen($formData['title']) > 255) {
        $errors[] = 'Le titre ne doit pas depasser 255 caracteres';
    }
    
    // Valider le statut et les transitions
    $allowedTransitions = [
        'draft' => ['draft', 'published'],
        'published' => ['published', 'archived'],
        'archived' => ['archived', 'published']
    ];
    
    if (!isset($allowedTransitions[$recipe['status']]) || 
        !in_array($formData['status'], $allowedTransitions[$recipe['status']])) {
        $errors[] = 'Transition de statut non autorisee';
        $formData['status'] = $recipe['status'];
    }
    
    // Filtrer les ingredients vides
    $validIngredients = array_filter($formData['ingredients'], function($ing) {
        return !empty(trim($ing['name'] ?? ''));
    });
    
    // Filtrer les etapes vides
    $validSteps = array_filter($formData['steps'], function($step) {
        return !empty(trim($step));
    });
    
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            $oldStatus = $recipe['status'];
            
            // Mettre a jour la recette
            $stmt = $pdo->prepare('UPDATE recipe SET title = ?, description = ?, status = ? WHERE id = ?');
            $stmt->execute([$formData['title'], $formData['description'], $formData['status'], $recipeId]);
            
            // Supprimer les anciens ingredients
            $pdo->prepare('DELETE FROM recipe_ingredient WHERE recipe_id = ?')->execute([$recipeId]);
            
            // Inserer les nouveaux ingredients
            foreach ($validIngredients as $ing) {
                $ingredientName = trim($ing['name']);
                $ingredientQuantity = trim($ing['quantity'] ?? '');
                
                // Verifier si l'ingredient existe, sinon le creer
                $stmt = $pdo->prepare('SELECT id FROM ingredient WHERE name = ?');
                $stmt->execute([$ingredientName]);
                $ingredientId = $stmt->fetchColumn();
                
                if (!$ingredientId) {
                    $stmt = $pdo->prepare('INSERT INTO ingredient (name) VALUES (?)');
                    $stmt->execute([$ingredientName]);
                    $ingredientId = $pdo->lastInsertId();
                }
                
                // Lier l'ingredient a la recette
                $stmt = $pdo->prepare('INSERT INTO recipe_ingredient (recipe_id, ingredient_id, quantity) VALUES (?, ?, ?)');
                $stmt->execute([$recipeId, $ingredientId, $ingredientQuantity]);
            }
            
            // Supprimer les anciennes etapes
            $pdo->prepare('DELETE FROM step WHERE recipe_id = ?')->execute([$recipeId]);
            
            // Inserer les nouvelles etapes
            $stepNumber = 1;
            foreach ($validSteps as $stepContent) {
                $stmt = $pdo->prepare('INSERT INTO step (recipe_id, step_number, content) VALUES (?, ?, ?)');
                $stmt->execute([$recipeId, $stepNumber, trim($stepContent)]);
                $stepNumber++;
            }
            
            // Supprimer les anciens tags
            $pdo->prepare('DELETE FROM recipe_tag WHERE recipe_id = ?')->execute([$recipeId]);
            
            // Inserer les nouveaux tags
            foreach ($formData['tags'] as $tagId) {
                $stmt = $pdo->prepare('INSERT IGNORE INTO recipe_tag (recipe_id, tag_id) VALUES (?, ?)');
                $stmt->execute([$recipeId, (int)$tagId]);
            }
            
            // Enregistrer l'historique de statut si change
            if ($oldStatus !== $formData['status']) {
                logStatusChange($pdo, 'recipe', $recipeId, $oldStatus, $formData['status'], $_SESSION['user_id']);
            }
            
            $pdo->commit();
            
            redirect('recipe.php?id=' . $recipeId, 'Recette mise a jour avec succes !', 'success');
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Une erreur est survenue lors de la mise a jour de la recette';
        }
    }
}

require_once '../includes/header.php';
?>

<div class="container">
    <?= displayFlashMessage() ?>
    
    <div class="page-header">
        <h1 class="page-title">Modifier la recette</h1>
        <p class="page-subtitle"><?= htmlspecialchars($recipe['title']) ?></p>
    </div>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul style="margin: 0; padding-left: 1.5rem;">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <form method="post" class="card-body">
            <!-- Informations de base -->
            <div class="form-group">
                <label class="form-label" for="title">Titre de la recette *</label>
                <input type="text" id="title" name="title" class="form-control" 
                       value="<?= htmlspecialchars($formData['title']) ?>" 
                       placeholder="Ex: Tarte aux pommes traditionnelle" required>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="description">Description</label>
                <textarea id="description" name="description" class="form-control" 
                          placeholder="Decrivez votre recette en quelques lignes..."><?= htmlspecialchars($formData['description']) ?></textarea>
            </div>
            
            <!-- Ingredients -->
            <div class="form-group">
                <label class="form-label">Ingredients</label>
                <div id="ingredients-container">
                    <?php foreach ($formData['ingredients'] as $index => $ing): ?>
                        <div class="form-row mb-1 ingredient-row">
                            <div class="form-group" style="flex: 2; margin-bottom: 0;">
                                <input type="text" name="ingredients[<?= $index ?>][name]" class="form-control" 
                                       value="<?= htmlspecialchars($ing['name']) ?>" placeholder="Nom de l'ingredient">
                            </div>
                            <div class="form-group" style="flex: 1; margin-bottom: 0;">
                                <input type="text" name="ingredients[<?= $index ?>][quantity]" class="form-control" 
                                       value="<?= htmlspecialchars($ing['quantity'] ?? '') ?>" placeholder="Quantite">
                            </div>
                            <button type="button" class="btn btn-danger btn-sm remove-ingredient" title="Supprimer">X</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" id="add-ingredient" class="btn btn-secondary btn-sm mt-1">+ Ajouter un ingredient</button>
            </div>
            
            <!-- Etapes -->
            <div class="form-group">
                <label class="form-label">Etapes de preparation</label>
                <div id="steps-container">
                    <?php foreach ($formData['steps'] as $index => $step): ?>
                        <div class="form-row mb-1 step-row">
                            <span class="step-number" style="display: flex; align-items: center; font-weight: bold; padding: 0 1rem;"><?= $index + 1 ?>.</span>
                            <div class="form-group" style="flex: 1; margin-bottom: 0;">
                                <textarea name="steps[]" class="form-control" rows="2" 
                                          placeholder="Decrivez cette etape..."><?= htmlspecialchars($step) ?></textarea>
                            </div>
                            <button type="button" class="btn btn-danger btn-sm remove-step" title="Supprimer">X</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" id="add-step" class="btn btn-secondary btn-sm mt-1">+ Ajouter une etape</button>
            </div>
            
            <!-- Tags -->
            <?php if (!empty($allTags)): ?>
                <div class="form-group">
                    <label class="form-label">Categories / Tags</label>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.75rem;">
                        <?php foreach ($allTags as $tag): ?>
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                <input type="checkbox" name="tags[]" value="<?= $tag['id'] ?>" 
                                       <?= in_array($tag['id'], $formData['tags']) ? 'checked' : '' ?>>
                                <?= htmlspecialchars($tag['name']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Statut -->
            <div class="form-group">
                <label class="form-label">Statut</label>
                <select name="status" class="form-control" style="max-width: 300px;">
                    <?php if ($recipe['status'] === 'draft'): ?>
                        <option value="draft" <?= $formData['status'] === 'draft' ? 'selected' : '' ?>>Brouillon</option>
                        <option value="published" <?= $formData['status'] === 'published' ? 'selected' : '' ?>>Publie</option>
                    <?php elseif ($recipe['status'] === 'published'): ?>
                        <option value="published" <?= $formData['status'] === 'published' ? 'selected' : '' ?>>Publie</option>
                        <option value="archived" <?= $formData['status'] === 'archived' ? 'selected' : '' ?>>Archive</option>
                    <?php elseif ($recipe['status'] === 'archived'): ?>
                        <option value="archived" <?= $formData['status'] === 'archived' ? 'selected' : '' ?>>Archive</option>
                        <option value="published" <?= $formData['status'] === 'published' ? 'selected' : '' ?>>Republier</option>
                    <?php endif; ?>
                </select>
                <p class="form-hint">Statut actuel : <strong><?= $recipe['status'] ?></strong></p>
            </div>
            
            <!-- Boutons -->
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                <a href="recipe.php?id=<?= $recipeId ?>" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

<script>
// Gestion dynamique des ingredients
let ingredientIndex = <?= count($formData['ingredients']) ?>;
document.getElementById('add-ingredient').addEventListener('click', function() {
    const container = document.getElementById('ingredients-container');
    const row = document.createElement('div');
    row.className = 'form-row mb-1 ingredient-row';
    row.innerHTML = `
        <div class="form-group" style="flex: 2; margin-bottom: 0;">
            <input type="text" name="ingredients[${ingredientIndex}][name]" class="form-control" placeholder="Nom de l'ingredient">
        </div>
        <div class="form-group" style="flex: 1; margin-bottom: 0;">
            <input type="text" name="ingredients[${ingredientIndex}][quantity]" class="form-control" placeholder="Quantite">
        </div>
        <button type="button" class="btn btn-danger btn-sm remove-ingredient" title="Supprimer">X</button>
    `;
    container.appendChild(row);
    ingredientIndex++;
});

document.getElementById('ingredients-container').addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-ingredient')) {
        const rows = document.querySelectorAll('.ingredient-row');
        if (rows.length > 1) {
            e.target.closest('.ingredient-row').remove();
        }
    }
});

// Gestion dynamique des etapes
document.getElementById('add-step').addEventListener('click', function() {
    const container = document.getElementById('steps-container');
    const stepCount = container.querySelectorAll('.step-row').length + 1;
    const row = document.createElement('div');
    row.className = 'form-row mb-1 step-row';
    row.innerHTML = `
        <span class="step-number" style="display: flex; align-items: center; font-weight: bold; padding: 0 1rem;">${stepCount}.</span>
        <div class="form-group" style="flex: 1; margin-bottom: 0;">
            <textarea name="steps[]" class="form-control" rows="2" placeholder="Decrivez cette etape..."></textarea>
        </div>
        <button type="button" class="btn btn-danger btn-sm remove-step" title="Supprimer">X</button>
    `;
    container.appendChild(row);
});

document.getElementById('steps-container').addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-step')) {
        const rows = document.querySelectorAll('.step-row');
        if (rows.length > 1) {
            e.target.closest('.step-row').remove();
            // Renumeroter
            document.querySelectorAll('.step-number').forEach((span, i) => {
                span.textContent = (i + 1) + '.';
            });
        }
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
