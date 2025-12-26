<?php echo $this->element('navbar'); ?>
<?php echo $this->element('sidebar'); ?>

<main class="app-main">
    <!-- Header -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0">Admin Dashboard</h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Admin Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="app-content">
        <div class="container-fluid">

            <!-- Stats cards -->
            <div class="row">
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon text-bg-primary shadow-sm">
                            <i class="bi bi-gear-fill"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Policies</span>
                            <span class="info-box-number"><h3><?php echo (int)$stats['total']; ?></h3></span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon text-bg-danger shadow-sm">
                            <i class="bi bi-hand-thumbs-up-fill"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Active Policies</span>
                            <span class="info-box-number"><h3><?php echo (int)$stats['active']; ?></h3></span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon text-bg-success shadow-sm">
                            <i class="bi bi-cart-fill"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Draft Policies</span>
                            <span class="info-box-number"><h3><?php echo (int)$stats['draft']; ?></h3></span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon text-bg-warning shadow-sm">
                            <i class="bi bi-people-fill"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Archived Policies</span>
                            <span class="info-box-number"><h3><?php echo (int)$stats['archived']; ?></h3></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Policy list card -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0">Policy List</h3>
                          

                        </div>

                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover text-nowrap">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Policy Number</th>
                                    <th>Sum Insured</th>
                                    <th>Premium Amount</th>
                                    <th>Status</th>
                                    <th class="text-center">Active</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if (!empty($policies) && is_array($policies)): ?>
                                    <?php foreach ($policies as $policy): ?>
                                        <tr>
                                            <td><?php echo h($policy['Policy']['id']); ?></td>
                                            <td><?php echo h($policy['Policy']['title']); ?></td>
                                            <td><?php echo h($policy['Policy']['description']); ?></td>
                                            <td><?php echo h($policy['Policy']['policy_no']); ?></td>
                                            <td><?php echo h($policy['Policy']['sum_insured']); ?></td>
                                            <td><?php echo h($policy['Policy']['premium_amount']); ?></td>
                                            <td><?php echo h($policy['Policy']['status']); ?></td>

                                            <!-- Active / inactive badge -->
                                            <td class="text-center">
                                                <?php
                                                $isActive = ($policy['Policy']['status'] === 'active');
                                                $btnClass = $isActive ? 'btn-success' : 'btn-secondary';
                                                ?>
                                                <button type="button"
                                                    class="btn btn-sm <?php echo $btnClass; ?> toggle-status-btn"
                                                    data-policy-id="<?php echo (int)$policy['Policy']['id']; ?>"
                                                    data-current-status="<?php echo h($policy['Policy']['status']); ?>">
                                                    <?php echo $isActive ? 'Active' : 'Inactive'; ?>
                                                </button>
                                            </td>

                                            <!-- Actions -->
                                            <td>
                                                <div class="btn-group" role="group" aria-label="policy-actions">
                                                    <?php
                                                    // View
                                                    echo $this->Html->link(
                                                        '<i class="bi bi-eye"></i>',
                                                        array('controller' => 'policies', 'action' => 'view', $policy['Policy']['id']),
                                                        array('class' => 'btn btn-outline-primary btn-sm', 'escape' => false, 'title' => 'View')
                                                    );

                                                    // Edit in modal
                                                    echo ' ' . $this->Html->link(
                                                        '<i class="bi bi-pencil"></i>',
                                                        'javascript:void(0);',
                                                        array(
                                                            'class' => 'btn btn-outline-warning btn-sm',
                                                            'escape' => false,
                                                            'title' => 'Edit',
                                                            'data-toggle' => 'modal',
                                                            'data-target' => '#policyModal',
                                                            'onclick' => sprintf(
                                                                "openPolicyModal(%d,'%s','%s');",
                                                                (int)$policy['Policy']['id'],
                                                                addslashes($policy['Policy']['title']),
                                                                addslashes($policy['Policy']['description'])
                                                            )
                                                        )
                                                    );

                                                    // Delete
                                                    echo ' ' . $this->Form->postLink(
                                                        '<i class="bi bi-trash"></i>',
                                                        array('controller' => 'policies', 'action' => 'delete', $policy['Policy']['id']),
                                                        array('class' => 'btn btn-outline-danger btn-sm', 'escape' => false, 'title' => 'Delete'),
                                                        'Are you sure you want to delete this policy?'
                                                    );
                                                    ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No policies found.</td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /.container-fluid -->
    </div><!-- /.app-content -->

    <!-- Add/Edit Policy Modal (title + description only) -->
    <div class="modal fade" id="policyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="policyModalTitle">Add Policy</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>

    <script>
    (function(){
        function getBaseToggleUrl() {
            return '<?php echo $this->Html->url(array('controller' => 'policies', 'action' => 'toggle_status'), true); ?>';
        }

        document.addEventListener('click', function(e){
            var btn = e.target.closest && e.target.closest('.toggle-status-btn');
            if (!btn) return;
            e.preventDefault();
            var id = btn.getAttribute('data-policy-id');
            if (!id) return;

            // Disable while request in progress
            btn.disabled = true;
            var url = getBaseToggleUrl() + '/' + id;

            fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(function(res){
                return res.json();
            }).then(function(payload){
                if (payload && payload.success) {
                    // Refresh the page so status and lists are consistent
                    window.location.reload();
                } else {
                    alert((payload && payload.message) ? payload.message : 'Status update failed');
                }
            }).catch(function(err){
                console.error(err);
                alert('Network error');
            }).finally(function(){
                btn.disabled = false;
            });
        });
    })();
    </script>
                <?php
                echo $this->Form->create('Policy', array(
                    'id'  => 'policyForm',
                    'url' => array('controller' => 'policies', 'action' => 'add')
                ));
                ?>
<div class="modal-body bg-light">
    <div class="border rounded p-3 mb-3 bg-white">
        <h6 class="mb-3 text-secondary">Basic Details</h6>

        <div class="row">
            <div class="col-md-6 mb-3">
                <?php echo $this->Form->input('title', array(
                    'label' => 'Policy Title',
                    'class' => 'form-control',
                    'placeholder' => 'e.g. Health Plus Gold'
                )); ?>
            </div>

            <div class="col-md-6 mb-3">
                <?php echo $this->Form->input('policy_no', array(
                    'label' => 'Policy Number',
                    'class' => 'form-control',
                    'placeholder' => 'e.g. POL-2025-0001'
                )); ?>
            </div>
        </div>

        <div class="mb-2">
            <?php echo $this->Form->input('description', array(
                'label' => 'Description',
                'class' => 'form-control',
                'type'  => 'textarea',
                'rows'  => 3,
                'placeholder' => 'Short summary of coverage, key terms, etc.'
            )); ?>
            <small class="form-text text-muted">
                Keep it brief; detailed wording can go in the full policy document.
            </small>
        </div>
    </div>

    <div class="border rounded p-3 bg-white">
        <h6 class="mb-3 text-secondary">Financials</h6>

        <div class="row">
            <div class="col-md-6 mb-3">
                <?php echo $this->Form->input('sum_insured', array(
                    'label' => 'Sum Insured',
                    'class' => 'form-control',
                    'type'  => 'number',
                    'step'  => '0.01',
                    'min'   => '0',
                    'placeholder' => 'e.g. 500000'
                )); ?>
                <small class="form-text text-muted">
                    Maximum coverage amount for this policy.
                </small>
            </div>

            <div class="col-md-6 mb-3">
                <?php echo $this->Form->input('premium_amount', array(
                    'label' => 'Premium Amount',
                    'class' => 'form-control',
                    'type'  => 'number',
                    'step'  => '0.01',
                    'min'   => '0',
                    'placeholder' => 'e.g. 15000'
                )); ?>
                <small class="form-text text-muted">
                    Premium per year (or your standard billing period).
                </small>
            </div>
        </div>
    </div>

    <?php echo $this->Form->hidden('id', array('id' => 'policyIdField')); ?>
</div>


                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <?php echo $this->Form->button('Save', array('class' => 'btn btn-primary')); ?>
                </div>

                <?php echo $this->Form->end(); ?>
            </div>
        </div>
    </div>

</main>

<?php $this->start('script'); ?>
<script>
function openPolicyModal(id, title, description) {
    var isEdit = !!id;

    // Modal title
    document.getElementById('policyModalTitle').innerText =
        isEdit ? 'Edit Policy' : 'Add Policy';

    // Form action
    var form = document.getElementById('policyForm');
    if (isEdit) {
        form.action = '<?php echo $this->Html->url(array(
            "controller" => "policies",
            "action" => "edit"
        )); ?>/' + id;
    } else {
        form.action = '<?php echo $this->Html->url(array(
            "controller" => "policies",
            "action" => "add"
        )); ?>';
    }

    // Fill fields
    document.getElementById('policyIdField').value     = id || '';
    document.getElementById('PolicyTitle').value       = title || '';
    document.getElementById('PolicyDescription').value = description || '';
}
</script>
<?php $this->end(); ?>

<?php echo $this->element('footer'); ?>
