<!-- File: View/Users/manage_users.ctp -->
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>User Management</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <?php echo $this->Html->link('Home', array('controller' => 'users', 'action' => 'dashboard')); ?>
                        </li>
                        <li class="breadcrumb-item active">Manage Users</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- User Statistics -->
            <div class="row">
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?php echo count($users); ?></h3>
                            <p>Total Users</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <?php 
                            $activeCount = 0;
                            foreach ($users as $u) {
                                if ($u['User']['status'] == 1) $activeCount++;
                            }
                            ?>
                            <h3><?php echo $activeCount; ?></h3>
                            <p>Active Users</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?php echo count($users) - $activeCount; ?></h3>
                            <p>Inactive Users</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">All Users</h3>
                            <div class="card-tools">
                                <div class="input-group input-group-sm" style="width: 250px;">
                                    <input type="text" id="searchInput" class="form-control float-right" placeholder="Search users...">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-default">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body table-responsive p-0" style="height: 500px;">
                            <table class="table table-head-fixed text-nowrap" id="usersTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Full Name</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($users)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center">No users found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
                                                <td><?php echo h($user['User']['id']); ?></td>
                                                <td><?php echo h($user['User']['full_name']); ?></td>
                                                <td><?php echo h($user['User']['username']); ?></td>
                                                <td><?php echo h($user['User']['email']); ?></td>
                                                <td>
                                                    <?php if ($user['User']['role'] === 'user'): ?>
                                                        <span class="badge badge-success">User</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-info">Guest</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($user['User']['status'] == 1): ?>
                                                        <span class="badge badge-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo h($user['User']['created']); ?></td>
                                                <td>
                                                    <div class="btn-group">
                                                        <?php echo $this->Html->link(
                                                            '<i class="fas fa-eye"></i>',
                                                            array('controller' => 'users', 'action' => 'view', $user['User']['id']),
                                                            array('class' => 'btn btn-info btn-sm', 'escape' => false, 'title' => 'View')
                                                        ); ?>
                                                        
                                                        <?php echo $this->Html->link(
                                                            '<i class="fas fa-edit"></i>',
                                                            array('controller' => 'users', 'action' => 'edit', $user['User']['id']),
                                                            array('class' => 'btn btn-warning btn-sm', 'escape' => false, 'title' => 'Edit')
                                                        ); ?>
                                                        
                                                        <?php echo $this->Form->postLink(
                                                            '<i class="fas fa-trash"></i>',
                                                            array('controller' => 'users', 'action' => 'delete', $user['User']['id']),
                                                            array('class' => 'btn btn-danger btn-sm', 'escape' => false, 'title' => 'Delete'),
                                                            'Are you sure you want to delete this user?'
                                                        ); ?>
                                                        
                                                        <?php echo $this->Form->postLink(
                                                            $user['User']['status'] == 1 ? '<i class="fas fa-toggle-on"></i>' : '<i class="fas fa-toggle-off"></i>',
                                                            array('controller' => 'users', 'action' => 'toggle_status', $user['User']['id']),
                                                            array(
                                                                'class' => 'btn btn-' . ($user['User']['status'] == 1 ? 'success' : 'secondary') . ' btn-sm',
                                                                'escape' => false,
                                                                'title' => $user['User']['status'] == 1 ? 'Deactivate' : 'Activate'
                                                            )
                                                        ); ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
            </div>

            <!-- Back Button -->
            <div class="row">
                <div class="col-12">
                    <?php echo $this->Html->link(
                        '<i class="fas fa-arrow-left"></i> Back to Dashboard',
                        array('controller' => 'users', 'action' => 'dashboard'),
                        array('class' => 'btn btn-secondary', 'escape' => false)
                    ); ?>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>

<script>
// Simple search functionality
document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('searchInput');
    var table = document.getElementById('usersTable');
    var tr = table.getElementsByTagName('tr');
    
    searchInput.addEventListener('keyup', function() {
        var filter = searchInput.value.toUpperCase();
        
        for (var i = 1; i < tr.length; i++) {
            var td = tr[i].getElementsByTagName('td');
            var found = false;
            
            for (var j = 0; j < td.length; j++) {
                if (td[j]) {
                    var txtValue = td[j].textContent || td[j].innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }
            }
            
            if (found) {
                tr[i].style.display = '';
            } else {
                tr[i].style.display = 'none';
            }
        }
    });
});
</script>

<style>
.table-head-fixed thead tr {
    position: sticky;
    top: 0;
    background-color: #fff;
    z-index: 10;
}

.input-group {
    position: relative;
    display: flex;
    flex-wrap: wrap;
    align-items: stretch;
    width: 100%;
}

.input-group-append {
    margin-left: -1px;
}

.input-group-sm > .form-control {
    height: calc(1.8125rem + 2px);
    padding: .25rem .5rem;
    font-size: .875rem;
    line-height: 1.5;
    border-radius: .2rem;
}
</style>