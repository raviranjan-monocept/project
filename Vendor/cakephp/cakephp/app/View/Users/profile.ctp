<?php
// ---------------------------------------------------------------------
// Ensure $user['User'] exists so view does not throw undefined index
// ---------------------------------------------------------------------
if (!isset($user) || !isset($user['User'])) {
    if (!empty($this->request->data['User'])) {
        $user = array('User' => $this->request->data['User']);
    } else {
        $authUser = $this->Session->read('Auth.User');
        if (!empty($authUser)) {
            $user = array('User' => $authUser);
        } else {
            $user = array('User' => array());
        }
    }
}
?>

<?php echo $this->element('navbar'); ?>
<?php echo $this->element('sidebar'); ?>

<section class="content">
    <div class="container-fluid mt-3">
        <div class="row">
            <!-- Left column: profile card -->
            <div class="col-md-4">
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile text-center">
                        <div class="mb-3">
                            <?php
                            $imgPath = !empty($user['User']['image'])
                                ? $user['User']['image']
                                : 'default-user.png'; // put a default image in webroot/img/

                            echo $this->Html->image(
                                $imgPath,
                                array(
                                   'class' => 'rounded-circle shadow user-avatar',
                                   'alt'   => 'Care Health Insurance',
                                    'style' => 'width:120px;height:120px;object-fit:cover;'
                                )
                            );
                            ?>
                        </div>



                        <h3 class="profile-username text-center">
                            <?php echo !empty($user['User']['full_name']) ? h($user['User']['full_name']) : 'Your Name'; ?>
                        </h3>
                        <p class="text-muted text-center">
                            <?php
                            $role = !empty($user['User']['role']) ? $user['User']['role'] : 'user';
                            echo ucfirst(h($role));
                            ?>
                        </p>

                        <ul class="list-group list-group-unbordered mb-3 text-start">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-envelope me-2"></i>Email</span>
                                <span class="text-muted">
                                    <?php echo !empty($user['User']['email']) ? h($user['User']['email']) : '-'; ?>
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-phone me-2"></i>Mobile</span>
                                <span class="text-muted">
                                    <?php echo !empty($user['User']['mobile']) ? h($user['User']['mobile']) : '-'; ?>
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-map-marker-alt me-2"></i>Address</span>
                                <span class="text-muted text-end">
                                    <?php
                                    echo !empty($user['User']['address'])
                                        ? nl2br(h($user['User']['address']))
                                        : '-';
                                    ?>
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Right column: edit form -->
            <div class="col-md-8">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Edit Profile</h3>
                    </div>

                    <?php
                    echo $this->Form->create('User', array(
                        'type'  => 'file',
                        'class' => 'form-horizontal'
                    ));
                    ?>

                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-6">
                                <?php
                                echo $this->Form->input('full_name', array(
                                    'label'    => 'Full Name',
                                    'class'    => 'form-control',
                                    'required' => true
                                ));
                                ?>
                            </div>
                            <div class="col-sm-6">
                                <?php
                                echo $this->Form->input('email', array(
                                    'label'    => 'Email',
                                    'class'    => 'form-control',
                                    'required' => true
                                ));
                                ?>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-6">
                                <?php
                                echo $this->Form->input('mobile', array(
                                    'label' => 'Mobile Number',
                                    'class' => 'form-control'
                                ));
                                ?>
                            </div>
                            <div class="col-sm-6">
                                <?php
                                echo $this->Form->input('image', array(
                                    'label' => 'Profile Image',
                                    'type'  => 'file',
                                    'class' => 'form-control'
                                ));
                                ?>
                                <small class="form-text text-muted">
                                    JPG, PNG or GIF. Max ~2MB.
                                </small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <?php
                            echo $this->Form->input('address', array(
                                'label' => 'Address',
                                'type'  => 'textarea',
                                'rows'  => 3,
                                'class' => 'form-control'
                            ));
                            ?>
                        </div>
                    </div>

                    <div class="card-footer text-end">
                        <a href="<?php echo $this->Html->url(array('action' => 'dashboard')); ?>"
                           class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save Changes
                        </button>
                    </div>

                    <?php echo $this->Form->end(); ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php echo $this->element('footer'); ?>
