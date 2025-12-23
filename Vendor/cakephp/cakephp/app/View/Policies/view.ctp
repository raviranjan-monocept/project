<?php
/**
 * View Policy Detail
 * 
 * File: app/View/Policies/view.ctp
 */
?>
 <?php echo $this->element('navbar'); ?>
      <!--end::Header-->
      <!--begin::Sidebar-->
      <?php echo $this->element('sidebar'); ?>
      <!--begin::App Main-->
      <main class="app-main">
        <!--begin::App Content Header-->
        <div class="app-content-header">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
              <div class="col-sm-6"><h3 class="mb-0">Policy Details</h3></div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="#">Home</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Single policy detail</li>
                </ol>
              </div>
            </div>
            <!--end::Row-->
          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content Header-->
        <!--begin::App Content-->
        <div class="app-content">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row g-4">
              <!--begin::Col-->
              <div class="col-12">
                
              </div>
              <div class="col-md-12">
                                <div class="card card-primary card-outline mb-4">
                                    <div class="card-header d-flex align-items-center">
                                        <div class="card-title">Policy Name:- <?php echo h($policy['Policy']['title']); ?> </div>
                                        <div class="ms-auto">
                                            <div class="actions">
                                               <?php echo $this->Html->link(
                                           '<i class="fa fa-edit"></i> Edit',
                                              array('action' => 'edit', $policy['Policy']['id']),
                                           array('class' => 'btn btn-warning', 'escape' => false)
                                            ); ?>
                                           <?php echo $this->Form->postLink(
                                          '<i class="fa fa-trash"></i> Delete',
                                         array('action' => 'delete', $policy['Policy']['id']),
                                         array('class' => 'btn btn-danger', 'escape' => false),
                                         __('Are you sure you want to delete this policy?')
                                        ); ?>
                                        <?php echo $this->Html->link(
                                        '<i class="fa fa-arrow-left"></i> Back to List',
                                        array('action' => 'index'),
                                        array('class' => 'btn btn-default', 'escape' => false)
                                        ); ?>
                                       </div>
                                        </div>


                                    </div>

                                    <div class="card-body">
                                       <div class="panel-body">
                    <div class="policy-content">
                        <!-- Title -->
                        <div class="info-row">
                            <label><i class="fa fa-tag"></i> Title:</label>
                            <div class="info-value">
                                <h2><?php echo h($policy['Policy']['title']); ?></h2>
                            </div>
                        </div>
                         <div class="info-row">
                            <label><i class="fa fa-tag"></i> Policy Number:</label>
                            <div class="info-value">
                                <h2><?php echo h($policy['Policy']['policy_no']); ?></h2>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <div class="info-row">
                            <label><i class="fa fa-flag"></i> Status:</label>
                            <div class="info-value">
                                <?php
                                    $statusClass = '';
                                    $statusIcon = '';
                                    switch($policy['Policy']['status']) {
                                        case 'active':
                                            $statusClass = 'success';
                                            $statusIcon = 'check-circle';
                                            break;
                                        case 'draft':
                                            $statusClass = 'warning';
                                            $statusIcon = 'pencil';
                                            break;
                                        case 'archived':
                                            $statusClass = 'default';
                                            $statusIcon = 'archive';
                                            break;
                                    }
                                ?>
                                <h3>
                                    <span class="label label-<?php echo $statusClass; ?> status-badge">
                                        <i class="fa fa-<?php echo $statusIcon; ?>"></i>
                                        <?php echo strtoupper(h($policy['Policy']['status'])); ?>
                                    </span>
                                </h3>
                                
                            </div>
                        </div>
   <!-- Timestamps -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-row">
                                    <label><i class="fa fa-calendar-plus-o"></i> sum insured</label>
                                    <div class="info-value">
                                        <span class="date-time">
                                             <?php echo nl2br(h($policy['Policy']['sum_insured'])); ?>
                                        </span>
                                
                                       
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (!empty($policy['Policy']['modified'])): ?>
                                <div class="col-md-6">
                                    <div class="info-row">
                                        <label><i class="fa fa-calendar-check-o"></i>Premium Amount</label>
                                        <div class="info-value">
                                            <span class="date-time">
                                              <?php echo nl2br(h($policy['Policy']['premium_amount'])); ?>
                                            </span>
                                           
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <!-- Description -->
                        <div class="info-row">
                            <label><i class="fa fa-align-left"></i> Description:</label>
                            <div class="info-value">
                                <div class="description-box">
                                    <?php echo nl2br(h($policy['Policy']['description'])); ?>
                                </div>
                            </div>
                        </div>

                        <!-- Timestamps -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-row">
                                    <label><i class="fa fa-calendar-plus-o"></i> Created:</label>
                                    <div class="info-value">
                                        <span class="date-time">
                                            <?php echo $this->Time->format('F j, Y', $policy['Policy']['created']); ?>
                                        </span>
                                        <br>
                                        <small class="text-muted">
                                            <?php echo $this->Time->format('h:i A', $policy['Policy']['created']); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (!empty($policy['Policy']['modified'])): ?>
                                <div class="col-md-6">
                                    <div class="info-row">
                                        <label><i class="fa fa-calendar-check-o"></i> Last Modified:</label>
                                        <div class="info-value">
                                            <span class="date-time">
                                                <?php echo $this->Time->format('F j, Y', $policy['Policy']['modified']); ?>
                                            </span>
                                            <br>
                                            <small class="text-muted">
                                                <?php echo $this->Time->format('h:i A', $policy['Policy']['modified']); ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

                                    </div>
               
              </div>

             
            </div>

          </div>

        </div>
        <!--end::App Content-->
      </main>
      <!--end::App Main-->

<style>
.policies-view {
    padding: 20px;
}

.page-header {
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 2px solid #e0e0e0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.page-header h1 {
    margin: 0;
    color: #333;
}

.page-header .actions {
    display: flex;
    gap: 10px;
}

.panel {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border: none;
    border-radius: 8px;
    margin-bottom: 30px;
}

.panel-info > .panel-heading {
    background-color: #3498db;
    border-color: #3498db;
    color: white;
}

.policy-content {
    padding: 20px;
}

.info-row {
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e0e0e0;
}

.info-row:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.info-row label {
    display: block;
    font-weight: 600;
    color: #555;
    margin-bottom: 10px;
    font-size: 14px;
    text-transform: uppercase;
}

.info-row label i {
    margin-right: 8px;
    color: #3498db;
}

.info-value {
    color: #333;
    font-size: 16px;
    line-height: 1.6;
}

.info-value h2 {
    margin: 0;
    color: #2c3e50;
    font-size: 28px;
    font-weight: 700;
}

.description-box {
    background-color: #f8f9fa;
    padding: 20px;
    border-radius: 6px;
    border-left: 4px solid #3498db;
    font-size: 15px;
    line-height: 1.8;
}

.status-badge {
    font-size: 16px;
    padding: 10px 20px;
    border-radius: 20px;
}

.status-badge i {
    margin-right: 8px;
}

.date-time {
    font-size: 16px;
    font-weight: 600;
    color: #2c3e50;
}

.text-muted {
    color: #7f8c8d;
}

.pull-right {
    float: right;
}

.btn-group-lg .btn {
    margin: 0 5px;
}

@media print {
    .page-header .actions,
    .panel:last-child {
        display: none;
    }
}
</style>

<?php echo $this->element('footer'); ?>