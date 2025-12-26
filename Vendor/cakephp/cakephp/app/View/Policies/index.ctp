<?php
/**
 * Policies Index View
 * Complete form with validation on all fields
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
          <h3 class="mb-0">All Policies</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end">
            <li class="breadcrumb-item">
              <?php echo $this->Html->link('Home', array('controller' => 'users', 'action' => 'dashboard')); ?>
            </li>
            <li class="breadcrumb-item active" aria-current="page">All Policies</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <div class="app-content">
    <div class="container-fluid">
      
      <!-- Statistics Cards -->
      <div class="row">
        <div class="col-12 col-sm-6 col-md-3">
          <div class="info-box">
            <span class="info-box-icon text-bg-primary shadow-sm">
              <i class="bi bi-file-earmark-text-fill"></i>
            </span>
            <div class="info-box-content">
              <span class="info-box-text">Total Policies</span>
              <span class="info-box-number">
                <h3><?php echo isset($stats['total']) ? $stats['total'] : 0; ?></h3>
              </span>
            </div>
          </div>
        </div>

        <div class="col-12 col-sm-6 col-md-3">
          <div class="info-box">
            <span class="info-box-icon text-bg-success shadow-sm">
              <i class="bi bi-check-circle-fill"></i>
            </span>
            <div class="info-box-content">
              <span class="info-box-text">Active Policies</span>
              <span class="info-box-number">
                <h3><?php echo isset($stats['active']) ? $stats['active'] : 0; ?></h3>
              </span>
            </div>
          </div>
        </div>

        <div class="col-12 col-sm-6 col-md-3">
          <div class="info-box">
            <span class="info-box-icon text-bg-warning shadow-sm">
              <i class="bi bi-pencil-fill"></i>
            </span>
            <div class="info-box-content">
              <span class="info-box-text">Draft Policies</span>
              <span class="info-box-number">
                <h3><?php echo isset($stats['draft']) ? $stats['draft'] : 0; ?></h3>
              </span>
            </div>
          </div>
        </div>

        <div class="col-12 col-sm-6 col-md-3">
          <div class="info-box">
            <span class="info-box-icon text-bg-secondary shadow-sm">
              <i class="bi bi-archive-fill"></i>
            </span>
            <div class="info-box-content">
              <span class="info-box-text">Archived Policies</span>
              <span class="info-box-number">
                <h3><?php echo isset($stats['archived']) ? $stats['archived'] : 0; ?></h3>
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Policies Table -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Policy List</h3>
              <div class="card-tools">
                <button class="btn btn-primary btn-sm" type="button" onclick="openPolicyModal();">
                  <i class="bi bi-plus-lg"></i> Add Policy
                </button>
              </div>
            </div>
            
            <div class="card-body table-responsive p-0">
              <table class="table table-hover text-nowrap">
                <thead>
                  <tr>
                    <th style="width: 50px;">ID</th>
                    <th style="width: 250px;">Title</th>
                    <th style="width: 150px;">Category</th>
                    <th>Description</th>
                    <th style="width: 120px;">Status</th>
                    <th style="width: 130px;">Created On</th>
                    <th class="text-center" style="width: 150px;">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($policies)): ?>
                    <?php foreach ($policies as $policy): ?>
                      <tr>
                        <td><?php echo h($policy['Policy']['id']); ?></td>
                        <td>
                          <strong>
                            <?php echo $this->Html->link(
                              h($policy['Policy']['title']),
                              array('action' => 'view', $policy['Policy']['id']),
                              array('title' => 'View Details')
                            ); ?>
                          </strong>
                        </td>
                        <td>
                          <?php
                          if (!empty($policy['Category']['name'])) {
                            echo '<span class="badge bg-info">' . h($policy['Category']['name']) . '</span>';
                          } elseif (!empty($policy['Policy']['category_id']) && !empty($categories[$policy['Policy']['category_id']])) {
                            echo '<span class="badge bg-info">' . h($categories[$policy['Policy']['category_id']]) . '</span>';
                          } else {
                            echo '<span class="badge bg-secondary">Uncategorized</span>';
                          }
                          ?>
                        </td>
                        <td>
                          <?php 
                          $desc = isset($policy['Policy']['description']) ? h($policy['Policy']['description']) : '';
                          echo strlen($desc) > 60 ? substr($desc, 0, 60) . '...' : $desc;
                          ?>
                        </td>
                        <td>
                          <?php
                          $statusClass = 'secondary';
                          $statusIcon = 'bi-circle';
                          $status = isset($policy['Policy']['status']) ? $policy['Policy']['status'] : 'draft';
                          
                          switch($status) {
                            case 'active':
                              $statusClass = 'success';
                              $statusIcon = 'bi-check-circle-fill';
                              break;
                            case 'draft':
                              $statusClass = 'warning';
                              $statusIcon = 'bi-pencil-fill';
                              break;
                            case 'archived':
                              $statusClass = 'secondary';
                              $statusIcon = 'bi-archive-fill';
                              break;
                          }
                          ?>
                          <span class="badge bg-<?php echo $statusClass; ?>">
                            <i class="bi <?php echo $statusIcon; ?>"></i>
                            <?php echo ucfirst(h($status)); ?>
                          </span>
                        </td>
                        <td>
                          <small>
                            <?php 
                            if (isset($policy['Policy']['created'])) {
                              echo $this->Time->format('M d, Y', $policy['Policy']['created']);
                            }
                            ?>
                          </small>
                        </td>
                        <td class="text-center">
                          <div class="btn-group" role="group">
                            <?php
                            echo $this->Html->link(
                              '<i class="bi bi-eye"></i>',
                              array('action' => 'view', $policy['Policy']['id']),
                              array('class' => 'btn btn-outline-primary btn-sm', 'escape' => false, 'title' => 'View')
                            );

                            echo $this->Html->link(
                              '<i class="bi bi-pencil"></i>',
                              array('action' => 'edit', $policy['Policy']['id']),
                              array('class' => 'btn btn-outline-warning btn-sm', 'escape' => false, 'title' => 'Edit')
                            );

                            echo $this->Form->postLink(
                              '<i class="bi bi-trash"></i>',
                              array('action' => 'delete', $policy['Policy']['id']),
                              array('class' => 'btn btn-outline-danger btn-sm', 'escape' => false, 'title' => 'Delete'),
                              __('Are you sure you want to delete: %s?', $policy['Policy']['title'])
                            );
                            ?>
                          </div>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="7" class="text-center text-muted py-5">
                        <i class="bi bi-inbox" style="font-size: 48px; opacity: 0.5;"></i>
                        <p class="mt-2 mb-0">No policies found</p>
                        <small>Click "Add Policy" button to create your first policy</small>
                      </td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Add Policy Modal -->
      <div class="modal fade" id="policyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">
                <i class="bi bi-file-earmark-plus"></i> Add New Policy
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <?php echo $this->Form->create('Policy', array(
              'id' => 'policyForm',
              'url' => array('controller' => 'policies', 'action' => 'add'),
              'novalidate' => true
            )); ?>
            
            <div class="modal-body">
              <!-- Global Validation Alert -->
              <div id="globalAlert" class="alert alert-warning d-none" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <strong>Validation Error!</strong> 
                <span id="globalAlertMessage">Please fill all required fields correctly.</span>
              </div>

              <div class="row mb-3">
                <!-- Category Field - REQUIRED -->
                <div class="col-md-6">
                  <label class="form-label">
                    Category <span class="text-danger">*</span>
                  </label>
                  <?php echo $this->Form->input('category_id', array(
                    'type' => 'select',
                    'label' => false,
                    'options' => !empty($categories) ? $categories : array(),
                    'empty' => '-- Select Category --',
                    'class' => 'form-control',
                    'id' => 'policyCategorySelect',
                    'required' => true,
                    'div' => false
                  )); ?>
                  <small class="text-danger d-none" id="categoryError">
                    <i class="bi bi-exclamation-circle"></i> Please select a category
                  </small>
                </div>

                <!-- Title Field - REQUIRED -->
                <div class="col-md-6">
                  <label class="form-label">
                    Policy Title <span class="text-danger">*</span>
                  </label>
                  <?php echo $this->Form->input('title', array(
                    'label' => false,
                    'class' => 'form-control',
                    'placeholder' => 'e.g. Health Plus Gold',
                    'required' => true,
                    'id' => 'policyTitle',
                    'maxlength' => 200,
                    'div' => false
                  )); ?>
                  <small class="text-danger d-none" id="titleError">
                    <i class="bi bi-exclamation-circle"></i> Title is required (max 200 characters)
                  </small>
                </div>
              </div>

              <div class="row mb-3">
                <!-- Policy Number Field - REQUIRED -->
                <div class="col-md-6">
                  <label class="form-label">
                    Policy Number <span class="text-danger">*</span>
                  </label>
                  <?php echo $this->Form->input('policy_no', array(
                    'label' => false,
                    'class' => 'form-control',
                    'placeholder' => 'e.g. POL-2025-0001',
                    'required' => true,
                    'id' => 'policyNo',
                    'maxlength' => 50,
                    'div' => false
                  )); ?>
                  <small class="text-danger d-none" id="policyNoError">
                    <i class="bi bi-exclamation-circle"></i> Policy number is required
                  </small>
                </div>

                <!-- Sum Insured Field - REQUIRED -->
                <div class="col-md-6">
                  <label class="form-label">
                    Sum Insured <span class="text-danger">*</span>
                  </label>
                  <?php echo $this->Form->input('sum_insured', array(
                    'label' => false,
                    'class' => 'form-control',
                    'placeholder' => 'e.g. 500000',
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => '1',
                    'required' => true,
                    'id' => 'sumInsured',
                    'div' => false
                  )); ?>
                  <small class="text-danger d-none" id="sumInsuredError">
                    <i class="bi bi-exclamation-circle"></i> Sum insured must be greater than 0
                  </small>
                </div>
              </div>

              <!-- Description Field - REQUIRED -->
              <div class="mb-3">
                <label class="form-label">
                  Description <span class="text-danger">*</span>
                </label>
                <?php echo $this->Form->input('description', array(
                  'type' => 'textarea',
                  'label' => false,
                  'rows' => 4,
                  'class' => 'form-control',
                  'placeholder' => 'Brief description of policy coverage and key terms...',
                  'id' => 'policyDescription',
                  'div' => false
                )); ?>
              </div>

              <div class="row">
                <!-- Premium Amount Field - REQUIRED -->
                <div class="col-md-6">
                  <label class="form-label">
                    Premium Amount <span class="text-danger">*</span>
                  </label>
                  <?php echo $this->Form->input('premium_amount', array(
                    'label' => false,
                    'class' => 'form-control',
                    'placeholder' => 'e.g. 15000',
                    'type' => 'number',
                    'step' => '0.01',
                    'min' => '1',
                    'required' => true,
                    'id' => 'premiumAmount',
                    'div' => false
                  )); ?>
                  <small class="text-danger d-none" id="premiumAmountError">
                    <i class="bi bi-exclamation-circle"></i> Premium amount must be greater than 0
                  </small>
                </div>

                <!-- Status Field - REQUIRED -->
                <div class="col-md-6">
                  <label class="form-label">
                    Status <span class="text-danger">*</span>
                  </label>
                  <?php echo $this->Form->input('status', array(
                    'type' => 'select',
                    'label' => false,
                    'options' => array(
                      'draft' => 'Draft',
                      'active' => 'Active',
                      'archived' => 'Archived'
                    ),
                    'default' => 'draft',
                    'class' => 'form-control',
                    'id' => 'policyStatus',
                    'div' => false
                  )); ?>
                </div>
              </div>
            </div>
            
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                <i class="bi bi-x-circle"></i> Cancel
              </button>
              <?php echo $this->Form->submit('Save Policy', array(
                'class' => 'btn btn-primary',
                'id' => 'policySaveBtn'
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

#globalAlert {
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


.info-box {
  min-height: 90px;
}

.info-box-icon {
  width: 80px;
  height: 80px;
  line-height: 80px;
  font-size: 2rem;
}

.table > :not(caption) > * > * {
  padding: 0.75rem;
}

.text-muted {
  color: #6c757d !important;
  font-size: 0.875rem;
}
</style>

<script>
/**
 * Open modal and load categories dynamically
 */
function openPolicyModal() {
  var categoriesUrl = '<?php echo $this->Html->url(array("controller" => "categories", "action" => "index", "_ext" => "json"), true); ?>';
  var select = document.getElementById('policyCategorySelect');
  
  // Reset form and hide all errors
  var form = document.getElementById('policyForm');
  if (form) form.reset();
  hideAllErrors();
  
  if (select) {
    select.innerHTML = '<option value="">Loading categories...</option>';
    select.disabled = true;
  }

  fetch(categoriesUrl, {
    credentials: 'same-origin',
    headers: { 'Accept': 'application/json' }
  })
  .then(function(response) {
    if (!response.ok) throw new Error('Failed to load categories');
    return response.json();
  })
  .then(function(data) {
    if (!select) return;
    
    select.innerHTML = '<option value="">-- Select Category --</option>';
    select.disabled = false;
    
    if (data.success && Array.isArray(data.data) && data.data.length > 0) {
      data.data.forEach(function(item) {
        var cat = item.Category || item;
        if (cat.id && cat.name && cat.active == 1) {
          var option = document.createElement('option');
          option.value = cat.id;
          option.textContent = cat.name;
          select.appendChild(option);
        }
      });
    } else {
      select.innerHTML = '<option value="">No categories available</option>';
      select.disabled = true;
      showGlobalAlert('No categories found. Please create categories first.');
    }
  })
  .catch(function(error) {
    console.error('Error loading categories:', error);
    if (select) {
      select.innerHTML = '<option value="">Failed to load categories</option>';
      select.disabled = true;
    }
    showGlobalAlert('Failed to load categories. Please try again.');
  })
  .finally(function() {
    var modalEl = document.getElementById('policyModal');
    if (modalEl) {
      var modal = new bootstrap.Modal(modalEl);
      modal.show();
    }
  });
}

/**
 * Validation Helper Functions
 */
function showError(fieldId, errorId) {
  var field = document.getElementById(fieldId);
  var error = document.getElementById(errorId);
  
  if (field) field.classList.add('is-invalid');
  if (error) error.classList.remove('d-none');
}

function hideError(fieldId, errorId) {
  var field = document.getElementById(fieldId);
  var error = document.getElementById(errorId);
  
  if (field) field.classList.remove('is-invalid');
  if (error) error.classList.add('d-none');
}

function markValid(fieldId) {
  var field = document.getElementById(fieldId);
  if (field) {
    field.classList.remove('is-invalid');
    field.classList.add('is-valid');
  }
}

function hideAllErrors() {
  var allFields = ['policyCategorySelect', 'policyTitle', 'policyNo', 'sumInsured', 'premiumAmount'];
  var allErrors = ['categoryError', 'titleError', 'policyNoError', 'sumInsuredError', 'premiumAmountError'];
  
  allFields.forEach(function(fieldId) {
    var field = document.getElementById(fieldId);
    if (field) {
      field.classList.remove('is-invalid', 'is-valid');
    }
  });
  
  allErrors.forEach(function(errorId) {
    var error = document.getElementById(errorId);
    if (error) error.classList.add('d-none');
  });
  
  hideGlobalAlert();
}

function showGlobalAlert(message) {
  var alertBox = document.getElementById('globalAlert');
  var alertMessage = document.getElementById('globalAlertMessage');
  
  if (alertBox && alertMessage) {
    alertMessage.textContent = message;
    alertBox.classList.remove('d-none');
  }
}

function hideGlobalAlert() {
  var alertBox = document.getElementById('globalAlert');
  if (alertBox) alertBox.classList.add('d-none');
}

/**
  */
/**
 * Comprehensive Form Validation
 */
function validatePolicyForm() {
  var isValid = true;
  var errors = [];
  hideAllErrors();
  
  // Validate Category
  var categorySelect = document.getElementById('policyCategorySelect');
  if (!categorySelect || !categorySelect.value || categorySelect.value === '') {
    showError('policyCategorySelect', 'categoryError');
    errors.push('Category');
    if (isValid) categorySelect.focus();
    isValid = false;
  } else {
    markValid('policyCategorySelect');
  }
  
  // Validate Title
  var titleInput = document.getElementById('policyTitle');
  if (!titleInput || !titleInput.value.trim() || titleInput.value.trim().length < 3) {
    showError('policyTitle', 'titleError');
    errors.push('Title');
    if (isValid) titleInput.focus();
    isValid = false;
  } else {
    markValid('policyTitle');
  }
  
  // Validate Policy Number
  var policyNoInput = document.getElementById('policyNo');
  if (!policyNoInput || !policyNoInput.value.trim()) {
    showError('policyNo', 'policyNoError');
    errors.push('Policy Number');
    if (isValid) policyNoInput.focus();
    isValid = false;
  } else {
    markValid('policyNo');
  }
  
  // Validate Sum Insured
  var sumInsuredInput = document.getElementById('sumInsured');
  if (!sumInsuredInput || !sumInsuredInput.value || parseFloat(sumInsuredInput.value) <= 0) {
    showError('sumInsured', 'sumInsuredError');
    errors.push('Sum Insured');
    if (isValid) sumInsuredInput.focus();
    isValid = false;
  } else {
    markValid('sumInsured');
  }
  
  // Description: no client-side validation (server will handle if needed)
  
  // Validate Premium Amount
  var premiumInput = document.getElementById('premiumAmount');
  if (!premiumInput || !premiumInput.value || parseFloat(premiumInput.value) <= 0) {
    showError('premiumAmount', 'premiumAmountError');
    errors.push('Premium Amount');
    if (isValid) premiumInput.focus();
    isValid = false;
  } else {
    markValid('premiumAmount');
  }
  
  // Show global error message if validation fails
  if (!isValid) {
    showGlobalAlert('Please fix the following fields: ' + errors.join(', '));
  }
  
  return isValid;
}

/**
 * Initialize Event Listeners
 */
document.addEventListener('DOMContentLoaded', function() {
  
  // Real-time validation on category change
  var categorySelect = document.getElementById('policyCategorySelect');
  if (categorySelect) {
    categorySelect.addEventListener('change', function() {
      if (this.value) {
        hideError('policyCategorySelect', 'categoryError');
        markValid('policyCategorySelect');
      } else {
        this.classList.remove('is-valid');
      }
    });
  }
  
  // Real-time validation on title input
  var titleInput = document.getElementById('policyTitle');
  if (titleInput) {
    titleInput.addEventListener('input', function() {
      if (this.value.trim() && this.value.trim().length >= 3) {
        hideError('policyTitle', 'titleError');
        markValid('policyTitle');
      } else {
        this.classList.remove('is-valid');
      }
    });
  }
  
  // Real-time validation on policy number
  var policyNoInput = document.getElementById('policyNo');
  if (policyNoInput) {
    policyNoInput.addEventListener('input', function() {
      if (this.value.trim()) {
        hideError('policyNo', 'policyNoError');
        markValid('policyNo');
      } else {
        this.classList.remove('is-valid');
      }
    });
  }
  
  // Real-time validation on sum insured
  var sumInsuredInput = document.getElementById('sumInsured');
  if (sumInsuredInput) {
    sumInsuredInput.addEventListener('input', function() {
      if (this.value && parseFloat(this.value) > 0) {
        hideError('sumInsured', 'sumInsuredError');
        markValid('sumInsured');
      } else {
        this.classList.remove('is-valid');
      }
    });
  }
  
  // No real-time validation for description
  
  // Real-time validation on premium amount
  var premiumInput = document.getElementById('premiumAmount');
  if (premiumInput) {
    premiumInput.addEventListener('input', function() {
      if (this.value && parseFloat(this.value) > 0) {
        hideError('premiumAmount', 'premiumAmountError');
        markValid('premiumAmount');
      } else {
        this.classList.remove('is-valid');
      }
    });
  }
  
  // Handle form submission with AJAX
  var form = document.getElementById('policyForm');
  if (!form) return;

  form.addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validate all fields before submission
    if (!validatePolicyForm()) {
      return false;
    }
    
    var saveBtn = document.getElementById('policySaveBtn');
    if (saveBtn) {
      saveBtn.disabled = true;
      saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
    }

    var formData = new FormData(form);
    var payload = { Policy: {} };
    
    formData.forEach(function(value, key) {
      var match = key.match(/^data\[Policy\]\[(.+)\]$/);
      if (match) {
        payload.Policy[match[1]] = value;
      }
    });

    var url = '<?php echo $this->Html->url(array("controller" => "policies", "action" => "add", "_ext" => "json")); ?>';
    
    fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(payload)
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
      if (data.success) {
        var modalEl = document.getElementById('policyModal');
        var modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
        
        window.location.reload();
      } else {
        var errorMsg = data.message || 'Failed to save policy. Please check all fields.';
        showGlobalAlert(errorMsg);
        
        if (data.errors) {
          console.error('Server validation errors:', data.errors);
        }
      }
    })
    .catch(function(error) {
      console.error('Network error:', error);
      showGlobalAlert('Network error. Please check your connection and try again.');
    })
    .finally(function() {
      if (saveBtn) {
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="bi bi-save"></i> Save Policy';
      }
    });
    
    return false;
  });
});
</script>
