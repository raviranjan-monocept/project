<?php
/**
 * Categories Index View
 * Complete CRUD with validation on all fields
 */
?>
<?php echo $this->element('navbar'); ?>
<?php echo $this->element('sidebar'); ?>

<main class="app-main">
  <!-- Page Header -->
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-6">
          <h3 class="mb-0">All Categories</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end">
            <li class="breadcrumb-item">
              <?php echo $this->Html->link('Home', array('controller' => 'users', 'action' => 'dashboard')); ?>
            </li>
            <li class="breadcrumb-item active" aria-current="page">All Categories</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <div class="app-content">
    <div class="container-fluid">
      <?php
      // Calculate category statistics for dashboard cards
      $totalCategories = !empty($categories) ? count($categories) : 0;
      $activeCategories = 0;
      if (!empty($categories)) {
        foreach ($categories as $c) {
          $cat = isset($c['Category']) ? $c['Category'] : $c;
          if (isset($cat['active']) && ($cat['active'] == 1 || $cat['active'] === true)) {
            $activeCategories++;
          }
        }
      }
      $inactiveCategories = $totalCategories - $activeCategories;
      ?>

      <!-- Statistics Cards -->
      <div class="row mb-3">
        <div class="col-12 col-sm-6 col-md-3">
          <div class="info-box">
            <span class="info-box-icon text-bg-primary shadow-sm">
              <i class="bi bi-tags-fill"></i>
            </span>
            <div class="info-box-content">
              <span class="info-box-text">Total Categories</span>
              <span class="info-box-number"><h3><?php echo $totalCategories; ?></h3></span>
            </div>
          </div>
        </div>

        <div class="col-12 col-sm-6 col-md-3">
          <div class="info-box">
            <span class="info-box-icon text-bg-success shadow-sm">
              <i class="bi bi-check-circle-fill"></i>
            </span>
            <div class="info-box-content">
              <span class="info-box-text">Active Categories</span>
              <span class="info-box-number"><h3><?php echo $activeCategories; ?></h3></span>
            </div>
          </div>
        </div>

        <div class="col-12 col-sm-6 col-md-3">
          <div class="info-box">
            <span class="info-box-icon text-bg-warning shadow-sm">
              <i class="bi bi-toggle-off"></i>
            </span>
            <div class="info-box-content">
              <span class="info-box-text">Inactive Categories</span>
              <span class="info-box-number"><h3><?php echo $inactiveCategories; ?></h3></span>
            </div>
          </div>
        </div>

        <div class="col-12 col-sm-6 col-md-3">
          <div class="info-box">
            <span class="info-box-icon text-bg-secondary shadow-sm">
              <i class="bi bi-eye-slash-fill"></i>
            </span>
            <div class="info-box-content">
              <span class="info-box-text">Hidden / Archived</span>
              <span class="info-box-number"><h3>0</h3></span>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Categories Table Card -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="bi bi-tag-fill"></i> Category List
              </h3>
              <div class="card-tools">
                <button class="btn btn-primary btn-sm" type="button" onclick="openCategoryModal();">
                  <i class="bi bi-plus-lg"></i> Add Category
                </button>
              </div>
            </div>
            
            <div class="card-body table-responsive p-0">
              <table class="table table-hover text-nowrap">
                <thead>
                  <tr>
                    <th style="width: 50px;">ID</th>
                    <th style="width: 250px;">Name</th>
                    <th>Description</th>
                    <th style="width: 100px;" class="text-center">Status</th>
                    <th style="width: 150px;" class="text-center">Actions</th>
                  </tr>
                </thead>
                <tbody id="categoriesTbody">
                  <?php if (!empty($categories)) : ?>
                    <?php foreach ($categories as $c) :
                      $cat = isset($c['Category']) ? $c['Category'] : $c; ?>
                      <tr>
                        <td><?php echo h($cat['id']); ?></td>
                        <td><strong><?php echo h($cat['name']); ?></strong></td>
                        <td>
                          <?php 
                          $desc = isset($cat['description']) ? h($cat['description']) : '';
                          echo strlen($desc) > 50 ? substr($desc, 0, 50) . '...' : $desc;
                          ?>
                        </td>
                        <td class="text-center category-status-cell" data-id="<?php echo (int)$cat['id']; ?>">
                          <?php
                          $isActive = ($cat['active'] == 1 || $cat['active'] === true);
                          $badgeClass = $isActive ? 'success' : 'secondary';
                          $badgeIcon = $isActive ? 'check-circle-fill' : 'x-circle-fill';
                          ?>
                          <span class="badge bg-<?php echo $badgeClass; ?> status-badge">
                            <i class="bi bi-<?php echo $badgeIcon; ?>"></i>
                            <span class="status-text"><?php echo $isActive ? 'Active' : 'Inactive'; ?></span>
                          </span>
                        </td>
                        <td class="text-center">
                          <div class="btn-group" role="group">
                            <button class="btn btn-outline-warning btn-sm" 
                                    onclick="openCategoryModal(<?php echo (int)$cat['id']; ?>, <?php echo htmlspecialchars(json_encode($cat['name']), ENT_QUOTES, 'UTF-8'); ?>, <?php echo htmlspecialchars(json_encode(isset($cat['description']) ? $cat['description'] : ''), ENT_QUOTES, 'UTF-8'); ?>, <?php echo ($cat['active'] ? '1' : '0'); ?>);" 
                                    title="Edit">
                              <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-outline-danger btn-sm" 
                                    onclick="deleteCategory(<?php echo (int)$cat['id']; ?>, <?php echo htmlspecialchars(json_encode($cat['name']), ENT_QUOTES, 'UTF-8'); ?>);" 
                                    title="Delete">
                              <i class="bi bi-trash"></i>
                            </button>
                            <button class="btn btn-outline-<?php echo ($cat['active'] ? 'danger' : 'success'); ?> btn-sm" 
                                    onclick="toggleCategoryStatus(<?php echo (int)$cat['id']; ?>, this);" 
                                    title="<?php echo ($cat['active'] ? 'Deactivate' : 'Activate'); ?>">
                              <i class="bi <?php echo ($cat['active'] ? 'bi-toggle-on' : 'bi-toggle-off'); ?>"></i>
                            </button>
                          </div>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr id="emptyRow">
                      <td colspan="5" class="text-center text-muted py-5">
                        <i class="bi bi-inbox" style="font-size: 48px; opacity: 0.5;"></i>
                        <p class="mt-2 mb-0">No categories found</p>
                        <small>Click "Add Category" button to create your first category</small>
                      </td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Category Modal -->

<div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="categoryModalTitle">
          <i class="bi bi-tag-fill"></i> Add Category
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <?php echo $this->Form->create('Category', array(
        'id' => 'categoryForm',
        'url' => array('controller' => 'categories', 'action' => 'add')
      )); ?>
      
      <div class="modal-body">
        <!-- Name Field - REQUIRED -->
        <div class="mb-3">
          <label class="form-label">
            Category Name <span class="text-danger">*</span>
          </label>
          <?php echo $this->Form->input('name', array(
            'label' => false,
            'class' => 'form-control',
            'placeholder' => 'e.g. Health Insurance',
            'required' => true,
            'id' => 'CategoryName',
            'div' => false
          )); ?>
          <small class="text-danger d-none" id="nameError">
            <i class="bi bi-exclamation-circle"></i> Category name is required
          </small>
        </div>

        <!-- Description Field - REQUIRED -->
        <div class="mb-3">
          <label class="form-label">
            Description <span class="text-danger">*</span>
          </label>
          <?php echo $this->Form->input('description', array(
            'label' => false,
            'type' => 'textarea',
            'class' => 'form-control',
            'rows' => 3,
            'placeholder' => 'Brief description of the category...',
            'required' => true,
            'id' => 'CategoryDescription',
            'div' => false
          )); ?>
          <small class="text-danger d-none" id="descriptionError">
            <i class="bi bi-exclamation-circle"></i> Description is required
          </small>
        </div>

        <!-- Active Status -->
        <div class="mb-3">
          <div class="form-check form-switch">
            <?php echo $this->Form->input('active', array(
              'type' => 'checkbox',
              'label' => false,
              'checked' => true,
              'class' => 'form-check-input',
              'id' => 'CategoryActive',
              'div' => false
            )); ?>
            <label class="form-check-label" for="CategoryActive">
              Active (visible in dropdowns)
            </label>
          </div>
          <small class="text-muted">Inactive categories are hidden but not deleted</small>
        </div>

        <?php echo $this->Form->hidden('id', array('id' => 'categoryIdField')); ?>
      </div>
      
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="bi bi-x-circle"></i> Cancel
        </button>
        <?php echo $this->Form->submit('Save Category', array(
          'class' => 'btn btn-primary',
          'id' => 'categorySaveBtn'
        )); ?>
      </div>
      
      <?php echo $this->Form->end(); ?>
    </div>
  </div>
</div>


    </div>
  </div>
</main>

<?php echo $this->element('footer'); ?>

<style>
/* Validation Styles */
.is-invalid {
  border-color: #dc3545 !important;
  box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.is-valid {
  border-color: #198754 !important;
  box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
}

.text-danger {
  color: #dc3545 !important;
  font-size: 0.875rem;
  margin-top: 0.25rem;
  display: block;
}

#categoryAlert {
  margin-bottom: 1rem;
}

.spinner-border-sm {
  width: 1rem;
  height: 1rem;
  border-width: 0.15em;
}

.form-label {
  margin-bottom: 0.5rem;
  font-weight: 500;
}


.form-check-input {
  cursor: pointer;
}

.form-check-label {
  cursor: pointer;
}

.text-muted {
  color: #6c757d !important;
  font-size: 0.875rem;
}

.table > :not(caption) > * > * {
  padding: 0.75rem;
}
</style>
<script>
/**
 * API URLs
 */
const categoriesApiUrl = '<?php echo $this->Html->url(array('controller'=>'categories','action'=>'index','_ext'=>'json'), true); ?>';
const categoriesAddUrl = '<?php echo $this->Html->url(array('controller'=>'categories','action'=>'add','_ext'=>'json'), true); ?>';
const categoriesEditBase = '<?php echo $this->Html->url(array('controller'=>'categories','action'=>'edit'), true); ?>';
const categoriesDeleteBase = '<?php echo $this->Html->url(array('controller'=>'categories','action'=>'delete'), true); ?>';
const categoriesToggleUrl = '<?php echo $this->Html->url(array('controller'=>'categories','action'=>'toggle_status','_ext'=>'json'), true); ?>';

/**
 * Load categories
 */
function loadCategories() {
  fetch(categoriesApiUrl, {
    credentials: 'same-origin',
    headers: { 'Accept': 'application/json' }
  })
  .then(res => res.json())
  .then(payload => {
    if (payload.success) {
      renderCategories(payload.data);
    }
  })
  .catch(err => console.error('Error:', err));
}

/**
 * Render categories in table
 */
function renderCategories(categories) {
  const tbody = document.getElementById('categoriesTbody');
  tbody.innerHTML = '';
  
  if (!categories || categories.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="5" class="text-center text-muted py-5">
          <i class="bi bi-inbox" style="font-size: 48px; opacity: 0.5;"></i>
          <p class="mt-2 mb-0">No categories found</p>
          <small>Click "Add Category" button to create your first category</small>
        </td>
      </tr>
    `;
    return;
  }
  
  categories.forEach(c => {
    const cat = c.Category || c;
    const isActive = (cat.active == 1 || cat.active === true);
    const badgeClass = isActive ? 'success' : 'secondary';
    const badgeIcon = isActive ? 'check-circle-fill' : 'x-circle-fill';
    const statusText = isActive ? 'Active' : 'Inactive';
    const desc = cat.description || '';
    const shortDesc = desc.length > 50 ? desc.substring(0, 50) + '...' : desc;
    
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${escapeHtml(cat.id)}</td>
      <td><strong>${escapeHtml(cat.name)}</strong></td>
      <td>${escapeHtml(shortDesc)}</td>
      <td class="text-center category-status-cell" data-id="${cat.id}">
        <span class="badge bg-${badgeClass}">
          <i class="bi bi-${badgeIcon}"></i>
          <span class="status-text">${statusText}</span>
        </span>
      </td>
      <td class="text-center">
        <div class="btn-group" role="group">
          <button class="btn btn-outline-warning btn-sm" onclick="openCategoryModal(${cat.id}, ${JSON.stringify(cat.name)}, ${JSON.stringify(cat.description || '')}, ${cat.active ? 1 : 0});" title="Edit"><i class="bi bi-pencil"></i></button>
          <button class="btn btn-outline-danger btn-sm" onclick="deleteCategory(${cat.id}, ${JSON.stringify(cat.name)});" title="Delete"><i class="bi bi-trash"></i></button>
          <button class="btn btn-outline-${isActive ? 'danger' : 'success'} btn-sm" onclick="toggleCategoryStatus(${cat.id}, this);" title="${isActive ? 'Deactivate' : 'Activate'}"><i class="bi ${isActive ? 'bi-toggle-on' : 'bi-toggle-off'}"></i></button>
        </div>
      </td>
    `;
    tbody.appendChild(tr);
  });
}

function escapeHtml(text) {
  if (text === null || text === undefined) return '';
  return String(text).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

/**
 * Open modal
 */
function openCategoryModal(id, name, description, active) {
  id = id || null;
  
  // Clear errors
  document.getElementById('CategoryName').classList.remove('is-invalid', 'is-valid');
  document.getElementById('CategoryDescription').classList.remove('is-invalid', 'is-valid');
  document.getElementById('nameError').classList.add('d-none');
  document.getElementById('descriptionError').classList.add('d-none');
  
  if (!id) {
    document.getElementById('categoryModalTitle').innerHTML = '<i class="bi bi-tag-fill"></i> Add Category';
    document.getElementById('CategoryName').value = '';
    document.getElementById('CategoryDescription').value = '';
    document.getElementById('CategoryActive').checked = true;
    document.getElementById('categoryIdField').value = '';
    document.getElementById('categoryForm').action = '<?php echo $this->Html->url(array('controller'=>'categories','action'=>'add')); ?>';
  } else {
    document.getElementById('categoryModalTitle').innerHTML = '<i class="bi bi-pencil-fill"></i> Edit Category';
    document.getElementById('CategoryName').value = name || '';
    document.getElementById('CategoryDescription').value = description || '';
    document.getElementById('CategoryActive').checked = !!active;
    document.getElementById('categoryIdField').value = id;
    document.getElementById('categoryForm').action = '<?php echo $this->Html->url(array('controller'=>'categories','action'=>'edit')); ?>/' + id;
  }

  var modalEl = document.getElementById('categoryModal');
  if (modalEl) {
    var modal = new bootstrap.Modal(modalEl);
    modal.show();
  }
}

/**
 * Simple validation - just check if filled
 */
function validateCategory() {
  var isValid = true;
  
  var name = document.getElementById('CategoryName');
  var desc = document.getElementById('CategoryDescription');
  
  // Check name
  if (!name.value.trim()) {
    name.classList.add('is-invalid');
    document.getElementById('nameError').classList.remove('d-none');
    if (isValid) name.focus();
    isValid = false;
  } else {
    name.classList.remove('is-invalid');
    name.classList.add('is-valid');
    document.getElementById('nameError').classList.add('d-none');
  }
  
  // Check description
  if (!desc.value.trim()) {
    desc.classList.add('is-invalid');
    document.getElementById('descriptionError').classList.remove('d-none');
    if (isValid) desc.focus();
    isValid = false;
  } else {
    desc.classList.remove('is-invalid');
    desc.classList.add('is-valid');
    document.getElementById('descriptionError').classList.add('d-none');
  }
  
  return isValid;
}

/**
 * Toggle status
 */
function toggleCategoryStatus(id, btnEl) {
  if (!id) return;
  
  fetch(categoriesToggleUrl, {
    method: 'POST',
    credentials: 'same-origin',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({id: id})
  })
  .then(r => r.json())
  .then(resp => {
    if (resp && resp.success) {
      const cell = document.querySelector('.category-status-cell[data-id="' + id + '"]');
      if (cell) {
        const isActive = (resp.active == 1);
        const badgeClass = isActive ? 'success' : 'secondary';
        const badgeIcon = isActive ? 'check-circle-fill' : 'x-circle-fill';
        const statusText = isActive ? 'Active' : 'Inactive';
        
        cell.innerHTML = `
          <span class="badge bg-${badgeClass}">
            <i class="bi bi-${badgeIcon}"></i>
            <span class="status-text">${statusText}</span>
          </span>
        `;
      }
      
      if (btnEl) {
        btnEl.classList.remove('btn-outline-success', 'btn-outline-danger');
        if (resp.active == 1) {
          btnEl.classList.add('btn-outline-danger');
          btnEl.title = 'Deactivate';
          btnEl.innerHTML = '<i class="bi bi-toggle-on"></i>';
        } else {
          btnEl.classList.add('btn-outline-success');
          btnEl.title = 'Activate';
          btnEl.innerHTML = '<i class="bi bi-toggle-off"></i>';
        }
      }
    }
  })
  .catch(err => console.error('Error:', err));
}

/**
 * Delete category
 */
function deleteCategory(id, name) {
  if (!confirm('Are you sure you want to delete: "' + name + '"?')) return;
  
  fetch(categoriesDeleteBase + '/' + id + '.json', {
    method: 'POST',
    credentials: 'same-origin'
  })
  .then(r => r.json())
  .then(resp => {
    if (resp.success) loadCategories();
  })
  .catch(err => console.error('Error:', err));
}

/**
 * Initialize
 */
document.addEventListener('DOMContentLoaded', function() {
  loadCategories();
  
  // Real-time validation
  var nameInput = document.getElementById('CategoryName');
  if (nameInput) {
    nameInput.addEventListener('input', function() {
      if (this.value.trim()) {
        this.classList.remove('is-invalid');
        this.classList.add('is-valid');
        document.getElementById('nameError').classList.add('d-none');
      } else {
        this.classList.remove('is-valid');
      }
    });
  }
  
  var descInput = document.getElementById('CategoryDescription');
  if (descInput) {
    descInput.addEventListener('input', function() {
      if (this.value.trim()) {
        this.classList.remove('is-invalid');
        this.classList.add('is-valid');
        document.getElementById('descriptionError').classList.add('d-none');
      } else {
        this.classList.remove('is-valid');
      }
    });
  }
  
  // Form submit
  var form = document.getElementById('categoryForm');
  if (!form) return;

  form.addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Simple validation - just check if filled
    if (!validateCategory()) {
      return false;
    }
    
    const id = document.getElementById('categoryIdField').value;
    const name = document.getElementById('CategoryName').value.trim();
    const description = document.getElementById('CategoryDescription').value.trim();
    const active = document.getElementById('CategoryActive').checked ? 1 : 0;

    // Use FormData so CakePHP receives the expected POST fields and hidden tokens
    var formData = new FormData(form);
    // Ensure our manual fields are in the FormData (in case inputs were not produced by Form helper)
    // These keys match Cake's expected input names: data[Category][name], data[Category][description], data[Category][active]
    formData.set('data[Category][name]', name);
    formData.set('data[Category][description]', description);
    formData.set('data[Category][active]', active);

    var saveBtn = document.getElementById('categorySaveBtn');
    if (saveBtn) {
      saveBtn.disabled = true;
      saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';
    }

    const url = !id ? categoriesAddUrl : (categoriesEditBase + '/' + id + '.json');

    fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Accept': 'application/json' },
      body: formData
    })
    .then(function(res) {
      return res.text();
    })
    .then(function(text) {
      // If server returned HTML (starts with <), show helpful message
      var trimmed = text.trim();
      try {
        var resp = JSON.parse(trimmed);
      } catch (e) {
        console.error('Non-JSON response from server:', trimmed);
        alert('Save failed: ' + (trimmed.substring(0, 800)));
        return null;
      }
      return resp;
    })
    .then(function(resp) {
      if (!resp) return;
      if (resp.success) {
        var modalEl = document.getElementById('categoryModal');
        var modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
        // Reload index to reflect changes
        window.location.reload();
        return;
      }

      // Display server-side validation errors if present
      if (resp.errors) {
        if (resp.errors.name) {
          var nameEl = document.getElementById('CategoryName');
          if (nameEl) nameEl.classList.add('is-invalid');
          var ne = document.getElementById('nameError');
          if (ne) { ne.textContent = resp.errors.name.join(', '); ne.classList.remove('d-none'); }
        }
        if (resp.errors.description) {
          var descEl = document.getElementById('CategoryDescription');
          if (descEl) descEl.classList.add('is-invalid');
          var de = document.getElementById('descriptionError');
          if (de) { de.textContent = resp.errors.description.join(', '); de.classList.remove('d-none'); }
        }
      }
    })
    .catch(function(err) {
      console.error('Error submitting category:', err);
      alert('An error occurred while saving. See console for details.');
    })
    .finally(function() {
      if (saveBtn) {
        saveBtn.disabled = false;
        saveBtn.innerHTML = 'Save Category';
      }
    });
  });
});
</script>

