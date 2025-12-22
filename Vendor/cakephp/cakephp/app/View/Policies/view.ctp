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

<div class="app-content">
    <div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- Page Header -->
            <div class="page-header">
                <h1>
                    <i class="fa fa-file-text-o"></i> Policy Details
                </h1>
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

            <!-- Policy Information Card -->
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="fa fa-info-circle"></i> Policy Information
                        <span class="pull-right">
                            Policy Number: <?php echo h($policy['Policy']['id']); ?>
                        </span>
                    </h3>
                </div>
                <div class="panel-body">
                    <div class="policy-content">
                        <!-- Title -->
                        <div class="info-row">
                            <label><i class="fa fa-tag"></i> Title:</label>
                            <div class="info-value">
                                <h2><?php echo h($policy['Policy']['title']); ?></h2>
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

            <!-- Quick Actions Card -->
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="fa fa-bolt"></i> Quick Actions
                    </h3>
                </div>
                <div class="panel-body text-center">
                    <div class="btn-group btn-group-lg">
                        <?php echo $this->Html->link(
                            '<i class="fa fa-edit"></i> Edit Policy',
                            array('action' => 'edit', $policy['Policy']['id']),
                            array('class' => 'btn btn-warning', 'escape' => false)
                        ); ?>
                        
                        <?php echo $this->Html->link(
                            '<i class="fa fa-copy"></i> Duplicate',
                            array('action' => 'add'),
                            array('class' => 'btn btn-info', 'escape' => false)
                        ); ?>
                        
                        <?php echo $this->Html->link(
                            '<i class="fa fa-print"></i> Print',
                            '#',
                            array(
                                'class' => 'btn btn-default',
                                'escape' => false,
                                'onclick' => 'window.print(); return false;'
                            )
                        ); ?>
                        
                        <?php echo $this->Form->postLink(
                            '<i class="fa fa-trash"></i> Delete',
                            array('action' => 'delete', $policy['Policy']['id']),
                            array('class' => 'btn btn-danger', 'escape' => false),
                            __('Are you sure you want to delete this policy?')
                        ); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

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