<?php echo $this->element('navbar'); ?>
<?php echo $this->element('sidebar'); ?>
      <!--end::Header-->
      <!--begin::Sidebar-->
    
      <!--begin::App Main-->
    <!-- app/View/Customers/dashboard.ctp -->

<main class="app-main">
    <!-- Page header / breadcrumbs -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h3 class="mb-0">Customer Dashboard</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item">
                            <?php echo $this->Html->link('Home', ['controller' => 'customers', 'action' => 'dashboard']); ?>
                        </li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="app-content">
        <div class="container-fluid">

            <!-- Top summary cards -->
            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="card text-bg-primary mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Active Policies</h5>
                            <p class="card-text fs-3">
                                
                            </p>
                            <p class="mb-0 small text-light">Total policies currently active</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="card text-bg-success mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Next Premium Due</h5>
                            <p class="card-text fs-5">
                                
                            </p>
                            <p class="mb-0 small text-light">
                                
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="card text-bg-warning mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Open Claims</h5>
                            <p class="card-text fs-3">
                              
                            </p>
                            <p class="mb-0 small text-dark">Submitted / In review</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="card text-bg-info mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Unread Notifications</h5>
                            <p class="card-text fs-3">
                                
                            </p>
                            <p class="mb-0 small text-dark">Messages & alerts</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- My Policies & Payments -->
            <div class="row">
                <!-- My Policies -->
                <div class="col-lg-7">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">My Policies</h5>
                          
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-striped mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Policy No</th>
                                        <th>Product</th>
                                        <th>Sum Insured</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                   
                                        <tr>
                                            <td colspan="5" class="text-center py-3">
                                                You have no policies yet.
                                               
                                            </td>
                                        </tr>
                                 
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Payments & Premiums -->
                <div class="col-lg-5">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Upcoming Premiums</h5>
                         
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Policy</th>
                                        <th>Due Date</th>
                                        <th>Amount</th>
                                        <th class="text-end">Pay</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    
                                        <tr>
                                            <td colspan="4" class="text-center py-3">No upcoming premiums.</td>
                                        </tr>
                                   
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Claims & Support -->
            <div class="row">
                <!-- Recent Claims -->
                <div class="col-lg-6">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Recent Claims</h5>
                          
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-striped mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Claim No</th>
                                        <th>Policy</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    
                                        <tr>
                                            <td colspan="4" class="text-center py-3">No claims submitted yet.</td>
                                        </tr>
                                    
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Support tickets -->
                <div class="col-lg-6">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Support Tickets</h5>
                          
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Subject</th>
                                        <th>Status</th>
                                        <th>Updated</th>
                                    </tr>
                                </thead>
                                <tbody>
                                  
                                        <tr>
                                            <td colspan="4" class="text-center py-3">You have no support tickets.</td>
                                        </tr>
                                   
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick links to Profile & FAQ -->
            <div class="row">
                <div class="col-lg-6">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5>Profile Settings</h5>
                            <p class="mb-2">Update your personal information and security options.</p>
                         
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5>FAQ & Help Center</h5>
                            <p class="mb-2">Find quick answers about buying, renewing, and claiming.</p>
                           
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

     <?php echo $this->element('footer');?>