<!-- File: View/Users/add_admin.ctp -->
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Add Administrator</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <?php echo $this->Html->link('Home', array('controller' => 'users', 'action' => 'dashboard')); ?>
                        </li>
                        <li class="breadcrumb-item active">Add Admin</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Administrator Information</h3>
                        </div>
                        
                        <?php echo $this->Form->create('User', array('class' => 'form-horizontal')); ?>
                        
                        <div class="card-body">
                            <!-- Flash Messages -->
                            <?php echo $this->Session->flash(); ?>

                            <!-- Full Name -->
                            <div class="form-group row">
                                <label for="full_name" class="col-sm-3 col-form-label">Full Name <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <?php echo $this->Form->input('full_name', array(
                                        'class' => 'form-control',
                                        'label' => false,
                                        'placeholder' => 'Enter full name',
                                        'required' => true
                                    )); ?>
                                </div>
                            </div>

                            <!-- Username -->
                            <div class="form-group row">
                                <label for="username" class="col-sm-3 col-form-label">Username <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <?php echo $this->Form->input('username', array(
                                        'class' => 'form-control',
                                        'label' => false,
                                        'placeholder' => 'Enter username',
                                        'required' => true
                                    )); ?>
                                    <small class="form-text text-muted">Username must be at least 3 characters</small>
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="form-group row">
                                <label for="email" class="col-sm-3 col-form-label">Email Address <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <?php echo $this->Form->input('email', array(
                                        'type' => 'email',
                                        'class' => 'form-control',
                                        'label' => false,
                                        'placeholder' => 'Enter email address',
                                        'required' => true
                                    )); ?>
                                </div>
                            </div>

                            <!-- Password -->
                            <div class="form-group row">
                                <label for="password" class="col-sm-3 col-form-label">Password <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <?php echo $this->Form->input('password', array(
                                        'type' => 'password',
                                        'class' => 'form-control',
                                        'label' => false,
                                        'placeholder' => 'Enter password',
                                        'required' => true
                                    )); ?>
                                    <small class="form-text text-muted">Password must be at least 6 characters</small>
                                </div>
                            </div>

                            <!-- Role -->
                            <div class="form-group row">
                                <label for="role" class="col-sm-3 col-form-label">Role <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <?php echo $this->Form->input('role', array(
                                        'type' => 'select',
                                        'options' => array(
                                            'admin' => 'Admin',
                                            'super_user' => 'Super User'
                                        ),
                                        'class' => 'form-control',
                                        'label' => false,
                                        'required' => true
                                    )); ?>
                                    <small class="form-text text-muted">Select the administrator role</small>
                                </div>
                            </div>

                            <!-- Info Alert -->
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Note:</strong> An access code will be automatically generated for this administrator. 
                                Make sure to save it as it will only be shown once.
                            </div>
                        </div>

                        <div class="card-footer">
                            <div class="row">
                                <div class="col-sm-9 offset-sm-3">
                                    <?php echo $this->Form->submit('Create Administrator', array(
                                        'class' => 'btn btn-primary',
                                        'div' => false
                                    )); ?>
                                    
                                    <?php echo $this->Html->link('Cancel', 
                                        array('controller' => 'users', 'action' => 'dashboard'),
                                        array('class' => 'btn btn-secondary ml-2')
                                    ); ?>
                                </div>
                            </div>
                        </div>

                        <?php echo $this->Form->end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
.form-control {
    display: block;
    width: 100%;
    padding: .375rem .75rem;
    font-size: 1rem;
    line-height: 1.5;
    color: #495057;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid #ced4da;
    border-radius: .25rem;
}

.text-danger {
    color: #dc3545!important;
}

.card-primary {
    border-top: 3px solid #007bff;
}

.alert-info {
    color: #0c5460;
    background-color: #d1ecf1;
    border-color: #bee5eb;
    padding: .75rem 1.25rem;
    margin-bottom: 1rem;
    border: 1px solid transparent;
    border-radius: .25rem;
}
</style>