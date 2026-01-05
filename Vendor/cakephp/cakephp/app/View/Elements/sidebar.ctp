<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
  <!--begin::Sidebar Brand-->
  <div class="sidebar-brand">
    <!--begin::Brand Link-->
    <a href="<?php echo $this->Html->url(['controller' => 'users', 'action' => 'dashboard']); ?>" class="brand-link">
      <!--begin::Brand Image-->
      <?php echo $this->Html->image('careLogo.png', array('alt' => 'Care Health Insurance')); ?>
    </a>
    <!--end::Brand Link-->
  </div>
  <!--end::Sidebar Brand-->
  
  <!--begin::Sidebar Wrapper-->
  <div class="sidebar-wrapper">
    <nav class="mt-2">
      <!--begin::Sidebar Menu-->
      <ul
        class="nav sidebar-menu flex-column"
        data-lte-toggle="treeview"
        role="navigation"
        aria-label="Main navigation"
        data-accordion="false"
        id="navigation"
      >
        
        <?php $userRole = $this->Session->read('Auth.User.role'); ?>
        
        <!-- Dashboard - All Roles -->
        <li class="nav-item">
          <?= $this->Html->link(
            '<i class="nav-icon bi bi-speedometer2"></i><p>Dashboard</p>',
            ['controller' => 'users', 'action' => 'dashboard'],
            ['escape' => false, 'class' => 'nav-link', 'data-controller' => 'users', 'data-action' => 'dashboard']
          ) ?>
        </li>

        <?php if ($userRole === 'super_user'): ?>
          <!-- Super User Only - Admin Management -->
          <li class="nav-item">
            <?= $this->Html->link(
              '<i class="nav-icon bi bi-people-fill"></i><p>Admin Management</p>',
              ['controller' => 'Admins', 'action' => 'index'],
              ['escape' => false, 'class' => 'nav-link', 'data-controller' => 'admins', 'data-action' => 'index']
            ) ?>
          </li>
        <?php endif; ?>

        <?php if (in_array($userRole, ['admin', 'super_user'])): ?>
          <!-- Admin & Super User - Categories -->
          <li class="nav-item">
            <?= $this->Html->link(
              '<i class="nav-icon bi bi-box-seam-fill"></i><p>All Categories</p>',
              ['controller' => 'Categories', 'action' => 'index'],
              ['escape' => false, 'class' => 'nav-link', 'data-controller' => 'categories', 'data-action' => 'index']
            ) ?>
          </li>

          <!-- Admin & Super User - Policy Management -->
          <li class="nav-item">
            <?= $this->Html->link(
              '<i class="nav-icon bi bi-file-earmark-text"></i><p>Policy Management</p>',
              ['controller' => 'Policies', 'action' => 'index'],
              ['escape' => false, 'class' => 'nav-link', 'data-controller' => 'policies', 'data-action' => 'index']
            ) ?>
          </li>

          <!-- Admin & Super User - Claims & Approvals -->
          <li class="nav-item">
            <?= $this->Html->link(
              '<i class="nav-icon bi bi-clipboard-check"></i><p>Claims & Approvals</p>',
              ['controller' => 'Claims', 'action' => 'index'],
              ['escape' => false, 'class' => 'nav-link', 'data-controller' => 'claims', 'data-action' => 'index']
            ) ?>
          </li>

          <!-- Admin & Super User - Payments & Billing -->
          <li class="nav-item">
            <?= $this->Html->link(
              '<i class="nav-icon bi bi-credit-card"></i><p>Payments & Billing</p>',
              ['controller' => 'Payments', 'action' => 'index'],
              ['escape' => false, 'class' => 'nav-link', 'data-controller' => 'payments', 'data-action' => 'index']
            ) ?>
          </li>

          <!-- Admin & Super User - Reports & Analytics -->
          <li class="nav-item">
            <?= $this->Html->link(
              '<i class="nav-icon bi bi-graph-up"></i><p>Reports & Analytics</p>',
              ['controller' => 'Reports', 'action' => 'index'],
              ['escape' => false, 'class' => 'nav-link', 'data-controller' => 'reports', 'data-action' => 'index']
            ) ?>
          </li>
        <?php endif; ?>

        <?php if ($userRole === 'user'): ?>
          <!-- Customer Only - My Policies -->
          <li class="nav-item">
            <?= $this->Html->link(
              '<i class="nav-icon bi bi-file-earmark-text"></i><p>My Policies</p>',
              ['controller' => 'Policies', 'action' => 'my_policies'],
              ['escape' => false, 'class' => 'nav-link', 'data-controller' => 'policies', 'data-action' => 'my_policies']
            ) ?>
          </li>

          <!-- Customer Only - My Claims -->
          <li class="nav-item">
            <?= $this->Html->link(
              '<i class="nav-icon bi bi-clipboard-check"></i><p>My Claims</p>',
              ['controller' => 'Claims', 'action' => 'my_claims'],
              ['escape' => false, 'class' => 'nav-link', 'data-controller' => 'claims', 'data-action' => 'my_claims']
            ) ?>
          </li>

          <!-- Customer Only - Premium Payments -->
          <li class="nav-item">
            <?= $this->Html->link(
              '<i class="nav-icon bi bi-credit-card"></i><p>Premium Payments</p>',
              ['controller' => 'Payments', 'action' => 'my_payments'],
              ['escape' => false, 'class' => 'nav-link', 'data-controller' => 'payments', 'data-action' => 'my_payments']
            ) ?>
          </li>

          <!-- Customer Only - Support Tickets -->
          <li class="nav-item">
            <?= $this->Html->link(
              '<i class="nav-icon bi bi-headset"></i><p>Support Tickets</p>',
              ['controller' => 'Tickets', 'action' => 'index'],
              ['escape' => false, 'class' => 'nav-link', 'data-controller' => 'tickets', 'data-action' => 'index']
            ) ?>
          </li>
        <?php endif; ?>

        <?php if ($userRole === 'super_user'): ?>
          <!-- Super User Only - Manage FAQs -->
          <li class="nav-item">
            <?php echo $this->Html->link(
              '<i class="nav-icon bi bi-question-circle-fill"></i><p>Manage FAQs</p>',
              ['controller' => 'faqs', 'action' => 'manage'],
              ['escape' => false, 'class' => 'nav-link', 'data-controller' => 'faqs', 'data-action' => 'manage']
            ); ?>
          </li>
        <?php endif; ?>

        <!-- Profile Settings - All Roles -->
        <li class="nav-item">
          <?= $this->Html->link(
            '<i class="nav-icon bi bi-person-circle"></i><p>Profile Settings</p>',
            ['controller' => 'users', 'action' => 'profile'],
            ['escape' => false, 'class' => 'nav-link', 'data-controller' => 'users', 'data-action' => 'profile']
          ) ?>
        </li>

        <?php if ($userRole === 'user'): ?>
          <!-- Customer Only - Help & FAQ -->
          <li class="nav-item">
            <?= $this->Html->link(
              '<i class="nav-icon bi bi-question-circle"></i><p>Help & FAQ</p>',
              ['controller' => 'faqs', 'action' => 'index'],
              ['escape' => false, 'class' => 'nav-link', 'data-controller' => 'faqs', 'data-action' => 'index']
            ) ?>
          </li>
        <?php endif; ?>

      </ul>
      <!--end::Sidebar Menu-->
    </nav>
  </div>

  <!-- Sidebar Active Link Styling -->
  <style>
    .nav-link.active {
      background-color: #FFC107;
      color: #fff !important;
      border-radius: 4px;
      border-left: 4px solid #CC9900;
    }
    
    .nav-link.active i,
    .nav-link.active p {
      color: #fff !important;
    }
  </style>

  <!-- JavaScript to highlight active menu item -->
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    const currentController = '<?php echo strtolower($this->request->controller); ?>';
    const currentAction = '<?php echo strtolower($this->request->action); ?>';
    
    const navLinks = document.querySelectorAll('.nav-link[data-controller]');
    
    navLinks.forEach(link => {
      const linkController = link.getAttribute('data-controller').toLowerCase();
      const linkAction = link.getAttribute('data-action').toLowerCase();
      
      if (linkController === currentController && linkAction === currentAction) {
        link.classList.add('active');
        const navItem = link.closest('.nav-item');
        if (navItem) {
          navItem.classList.add('active');
        }
      }
    });
  });
  </script>
  <!--end::Sidebar Wrapper-->
</aside>
