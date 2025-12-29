<?php $this->assign('title', 'FAQ / Help Center'); ?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h3 class="mb-0">
                    <i class="bi bi-question-circle-fill text-primary"></i>
                    FAQ / Help Center
                </h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item">
                        <?php echo $this->Html->link('Home', ['controller' => 'users', 'action' => 'dashboard']); ?>
                    </li>
                    <li class="breadcrumb-item active">FAQ</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        
        <!-- Search Box -->
        <div class="row mb-4">
            <div class="col-lg-8 mx-auto">
                <div class="card">
                    <div class="card-body">
                        <?php echo $this->Form->create('Faq', array(
                            'url' => array('action' => 'search'),
                            'type' => 'get',
                            'class' => 'faq-search-form'
                        )); ?>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <?php echo $this->Form->input('q', array(
                                'label' => false,
                                'placeholder' => 'Search for answers...',
                                'class' => 'form-control',
                                'div' => false
                            )); ?>
                            <button class="btn btn-primary" type="submit">
                                Search
                            </button>
                        </div>
                        <?php echo $this->Form->end(); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- FAQ Categories -->
        <?php if (!empty($faqs)): ?>
            <?php foreach ($faqs as $category => $categoryFaqs): ?>
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="bi bi-folder-fill"></i>
                            <?php echo h($category); ?>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="accordion" id="accordion<?php echo str_replace(' ', '', $category); ?>">
                            <?php foreach ($categoryFaqs as $index => $faq): ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading<?php echo $faq['Faq']['id']; ?>">
                                        <button class="accordion-button <?php echo $index > 0 ? 'collapsed' : ''; ?>" 
                                                type="button" 
                                                data-bs-toggle="collapse" 
                                                data-bs-target="#collapse<?php echo $faq['Faq']['id']; ?>" 
                                                aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>" 
                                                aria-controls="collapse<?php echo $faq['Faq']['id']; ?>">
                                            <i class="bi bi-question-circle me-2"></i>
                                            <?php echo h($faq['Faq']['question']); ?>
                                            <?php if ($faq['Faq']['is_featured']): ?>
                                                <span class="badge bg-warning ms-2">Featured</span>
                                            <?php endif; ?>
                                        </button>
                                    </h2>
                                    <div id="collapse<?php echo $faq['Faq']['id']; ?>" 
                                         class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" 
                                         aria-labelledby="heading<?php echo $faq['Faq']['id']; ?>" 
                                         data-bs-parent="#accordion<?php echo str_replace(' ', '', $category); ?>">
                                        <div class="accordion-body">
                                            <p><?php echo nl2br(h($faq['Faq']['answer'])); ?></p>
                                            <hr>
                                            <small class="text-muted">
                                                <i class="bi bi-clock"></i>
                                                Last updated: <?php echo date('M d, Y', strtotime($faq['Faq']['modified'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
                    <h4 class="mt-3">No FAQs Available</h4>
                    <p class="text-muted">Check back later for helpful information.</p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Contact Support Card -->
        <div class="card border-primary">
            <div class="card-body text-center">
                <i class="bi bi-headset" style="font-size: 3rem; color: #0d6efd;"></i>
                <h4 class="mt-3">Still need help?</h4>
                <p class="text-muted">Can't find the answer you're looking for? Our support team is here to help.</p>
                <?php echo $this->Html->link(
                    '<i class="bi bi-envelope-fill"></i> Contact Support',
                    ['controller' => 'tickets', 'action' => 'add'],
                    ['class' => 'btn btn-primary btn-lg', 'escape' => false]
                ); ?>
            </div>
        </div>

    </div>
</div>
