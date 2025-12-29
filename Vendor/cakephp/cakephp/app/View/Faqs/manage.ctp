 <?php echo $this->element('navbar'); ?>
      <!--end::Header-->
      <!--begin::Sidebar-->
      <?php echo $this->element('sidebar'); ?>


        
        <!-- Main Content -->
        <main class="app-main">
            
            <div class="app-content-header">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">
                            <h3 class="mb-0">
                                <i class="bi bi-question-circle-fill text-primary"></i>
                                FAQ Management
                            </h3>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-end">
                                <li class="breadcrumb-item">
                                    <?php echo $this->Html->link('Home', ['controller' => 'users', 'action' => 'dashboard']); ?>
                                </li>
                                <li class="breadcrumb-item active">Manage FAQs</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="app-content">
                <div class="container-fluid">
                    
                    <!-- Action Bar -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card shadow-sm">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-0">Total FAQs: <span class="badge bg-primary"><?php echo !empty($faqs) ? count($faqs) : 0; ?></span></h5>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#addFaqModal">
                                            <i class="bi bi-plus-circle"></i> Add New FAQ
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Flash Messages -->
                    <?php if ($this->Session->check('Message.flash')): ?>
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-<?php echo ($this->Session->read('Message.flash.params.class') === 'success') ? 'success' : 'danger'; ?> alert-dismissible fade show">
                                    <i class="bi bi-<?php echo ($this->Session->read('Message.flash.params.class') === 'success') ? 'check-circle' : 'exclamation-triangle'; ?>-fill"></i>
                                    <?php echo $this->Session->flash(); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- FAQ List by Category -->
                    <?php if (!empty($faqs)): ?>
                        <?php foreach ($faqs as $category => $categoryFaqs): ?>
                            <div class="card mb-4 shadow-sm">
                                <div class="card-header bg-primary text-white">
                                    <h4 class="mb-0">
                                        <i class="bi bi-folder-fill"></i>
                                        <?php echo h($category); ?>
                                        <span class="badge bg-light text-dark float-end"><?php echo count($categoryFaqs); ?> FAQs</span>
                                    </h4>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th width="5%">ID</th>
                                                    <th width="30%">Question</th>
                                                    <th width="35%">Answer</th>
                                                    <th width="8%" class="text-center">Order</th>
                                                    <th width="10%" class="text-center">Featured</th>
                                                    <th width="12%" class="text-center">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($categoryFaqs as $faq): ?>
                                                    <tr>
                                                        <td><?php echo h($faq['Faq']['id']); ?></td>
                                                        <td>
                                                            <strong><?php echo h($faq['Faq']['question']); ?></strong>
                                                        </td>
                                                        <td>
                                                            <small class="text-muted">
                                                                <?php echo $this->Text->truncate(h($faq['Faq']['answer']), 100); ?>
                                                            </small>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge bg-info"><?php echo h($faq['Faq']['display_order']); ?></span>
                                                        </td>
                                                        <td class="text-center">
                                                            <?php if ($faq['Faq']['is_featured']): ?>
                                                                <span class="badge bg-warning text-dark"><i class="bi bi-star-fill"></i> Featured</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-secondary">Regular</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="text-center">
                                                            <button type="button" class="btn btn-sm btn-primary" 
                                                                    onclick="editFaq(<?php echo $faq['Faq']['id']; ?>)"
                                                                    title="Edit">
                                                                <i class="bi bi-pencil-square"></i>
                                                            </button>
                                                            <?php echo $this->Form->postLink(
                                                                '<i class="bi bi-trash-fill"></i>',
                                                                ['action' => 'delete', $faq['Faq']['id']],
                                                                [
                                                                    'class' => 'btn btn-sm btn-danger',
                                                                    'escape' => false,
                                                                    'confirm' => 'Are you sure you want to delete this FAQ?'
                                                                ]
                                                            ); ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="bi bi-inbox" style="font-size: 5rem; color: #ccc;"></i>
                                <h3 class="mt-3">No FAQs Available</h3>
                                <p class="text-muted">Click "Add New FAQ" button to create your first FAQ.</p>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
            
        </main>
        
        <!-- Footer -->
           <?php echo $this->element('footer'); ?>
    </div>

    <!-- Add/Edit FAQ Modal -->
    <div class="modal fade" id="addFaqModal" tabindex="-1" aria-labelledby="addFaqModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addFaqModalLabel">
                        <i class="bi bi-plus-circle"></i> Add New FAQ
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <?php echo $this->Form->create('Faq', array(
                    'url' => array('action' => 'add'),
                    'id' => 'faqForm',
                    'class' => 'needs-validation',
                    'novalidate' => true
                )); ?>
                
                <div class="modal-body">
                    
                    <!-- Category -->
                    <div class="mb-3">
                        <label for="category" class="form-label">
                            <i class="bi bi-folder"></i> Category <span class="text-danger">*</span>
                        </label>
                        <?php echo $this->Form->input('category', array(
                            'type' => 'select',
                            'options' => array(
                                'Policy Purchase' => 'Policy Purchase',
                                'Claims' => 'Claims',
                                'Payments' => 'Payments',
                                'Coverage' => 'Coverage',
                                'Renewal' => 'Renewal',
                                'General' => 'General'
                            ),
                            'empty' => '-- Select Category --',
                            'label' => false,
                            'class' => 'form-select form-select-lg',
                            'required' => true,
                            'id' => 'category',
                            'div' => false
                        )); ?>
                        <div class="invalid-feedback">Please select a category.</div>
                    </div>

                    <!-- Question -->
                    <div class="mb-3">
                        <label for="question" class="form-label">
                            <i class="bi bi-question-circle"></i> Question <span class="text-danger">*</span>
                        </label>
                        <?php echo $this->Form->input('question', array(
                            'type' => 'textarea',
                            'label' => false,
                            'class' => 'form-control form-control-lg',
                            'rows' => 3,
                            'placeholder' => 'Enter the FAQ question...',
                            'required' => true,
                            'id' => 'question',
                            'div' => false
                        )); ?>
                        <div class="invalid-feedback">Please enter a question.</div>
                    </div>

                    <!-- Answer -->
                    <div class="mb-3">
                        <label for="answer" class="form-label">
                            <i class="bi bi-chat-left-text"></i> Answer <span class="text-danger">*</span>
                        </label>
                        <?php echo $this->Form->input('answer', array(
                            'type' => 'textarea',
                            'label' => false,
                            'class' => 'form-control form-control-lg',
                            'rows' => 6,
                            'placeholder' => 'Enter the detailed answer...',
                            'required' => true,
                            'id' => 'answer',
                            'div' => false
                        )); ?>
                        <div class="invalid-feedback">Please enter an answer.</div>
                    </div>

                    <div class="row">
                        <!-- Display Order -->
                        <div class="col-md-6 mb-3">
                            <label for="displayOrder" class="form-label">
                                <i class="bi bi-sort-numeric-down"></i> Display Order
                            </label>
                            <?php echo $this->Form->input('display_order', array(
                                'type' => 'number',
                                'label' => false,
                                'class' => 'form-control',
                                'value' => 0,
                                'min' => 0,
                                'id' => 'displayOrder',
                                'div' => false
                            )); ?>
                            <small class="form-text text-muted">Lower numbers appear first</small>
                        </div>

                        <!-- Featured -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label d-block">
                                <i class="bi bi-star"></i> Featured
                            </label>
                            <div class="form-check form-switch">
                                <?php echo $this->Form->input('is_featured', array(
                                    'type' => 'checkbox',
                                    'label' => 'Mark as Featured FAQ',
                                    'class' => 'form-check-input',
                                    'div' => false,
                                    'id' => 'isFeatured',
                                    'style' => 'width: 3rem; height: 1.5rem;'
                                )); ?>
                            </div>
                            <small class="form-text text-muted">Featured FAQs appear on homepage</small>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-toggles"></i> Status
                        </label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="data[Faq][status]" id="statusActive" value="active" checked>
                            <label class="btn btn-outline-success" for="statusActive">
                                <i class="bi bi-check-circle"></i> Active
                            </label>

                            <input type="radio" class="btn-check" name="data[Faq][status]" id="statusInactive" value="inactive">
                            <label class="btn btn-outline-secondary" for="statusInactive">
                                <i class="bi bi-x-circle"></i> Inactive
                            </label>
                        </div>
                    </div>

                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Save FAQ
                    </button>
                </div>
                
                <?php echo $this->Form->end(); ?>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.3.0/browser/overlayscrollbars.browser.es6.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
    <?php echo $this->Html->script('adminlte.min'); ?>
    
    <script>
    // Form validation
    (function () {
        'use strict'
        var forms = document.querySelectorAll('.needs-validation')
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
    })()

    // Edit FAQ function
    function editFaq(faqId) {
        window.location.href = '<?php echo $this->Html->url(array("controller" => "faqs", "action" => "edit")); ?>/' + faqId;
    }

    // Reset form when modal is closed
    var addFaqModal = document.getElementById('addFaqModal');
    if (addFaqModal) {
        addFaqModal.addEventListener('hidden.bs.modal', function () {
            document.getElementById('faqForm').reset();
            document.getElementById('faqForm').classList.remove('was-validated');
        });
    }

    // Sidebar toggle
    const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
    const Default = {
        scrollbarTheme: 'os-theme-light',
        scrollbarAutoHide: 'leave',
        scrollbarClickScroll: true,
    };
    
    document.addEventListener('DOMContentLoaded', function () {
        const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
        if (sidebarWrapper && typeof OverlayScrollbarsGlobal?.OverlayScrollbars !== 'undefined') {
            OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
                scrollbars: {
                    theme: Default.scrollbarTheme,
                    autoHide: Default.scrollbarAutoHide,
                    clickScroll: Default.scrollbarClickScroll,
                },
            });
        }
    });
    </script>

    <style>
    .card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
    }

    .table tbody tr {
        transition: background-color 0.2s ease;
    }

    .table tbody tr:hover {
        background-color: rgba(0,123,255,0.05);
    }

    .modal-content {
        border: none;
        border-radius: 15px;
    }

    .modal-header {
        border-top-left-radius: 15px;
        border-top-right-radius: 15px;
    }

    .form-control:focus, .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13,110,253,0.25);
    }

    .btn {
        transition: all 0.3s ease;
    }

    .btn:hover {
        transform: translateY(-2px);
    }

    .badge {
        font-size: 0.85rem;
    }
    </style>
</body>
</html>
