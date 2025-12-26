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
               <li class="nav-item">
    <?= $this->Html->link(
      '<i class="nav-icon bi bi-palette"></i><p>Dashboard</p>',
      ['controller' => 'users', 'action' => 'dashboard'],
      ['escape' => false, 'class' => 'nav-link', 'data-controller' => 'users', 'data-action' => 'dashboard']
    ) ?>
  </li>
  

          <!-- Admin Management -->
  <li class="nav-item">
    <?= $this->Html->link(
      '<i class="nav-icon bi bi-palette"></i><p>Admin Management</p>',
      ['controller' => 'Admins', 'action' => 'index'],
      ['escape' => false, 'class' => 'nav-link', 'data-controller' => 'admins', 'data-action' => 'index']
    ) ?>
  </li>
          </li>
 
              <li class="nav-item">
    <?= $this->Html->link(
      '<i class="nav-icon bi bi-box-seam-fill"></i><p>All Categories</p>',
      ['controller' => 'Categories', 'action' => 'index'],
      ['escape' => false, 'class' => 'nav-link', 'data-controller' => 'categories', 'data-action' => 'index']
    ) ?>
              </li>

                     <!-- Policy Management -->
  <li class="nav-item">
    <?= $this->Html->link(
      '<i class="nav-icon bi bi-palette"></i><p>Policy Management</p>',
      ['controller' => 'Policies', 'action' => 'index'],
      ['escape' => false, 'class' => 'nav-link', 'data-controller' => 'policies', 'data-action' => 'index']
    ) ?>
  </li>
              
              <li class="nav-item">
    <?= $this->Html->link(
      '<i class="nav-icon bi bi-palette"></i><p>Claims & Approvals</p>',
      ['controller' => 'Claims', 'action' => 'index'],
      ['escape' => false, 'class' => 'nav-link', 'data-controller' => 'claims', 'data-action' => 'index']
    ) ?>
              </li>
 

              <li class="nav-item">
    <?= $this->Html->link(
      '<i class="nav-icon bi bi-download"></i><p>Payments & Billing</p>',
      ['controller' => 'Payments', 'action' => 'index'],
      ['escape' => false, 'class' => 'nav-link', 'data-controller' => 'payments', 'data-action' => 'index']
    ) ?>
              </li>
             <li class="nav-item">
    <?= $this->Html->link(
      '<i class="nav-icon bi bi-download"></i><p>Reports & Analytics</p>',
      ['controller' => 'Reports', 'action' => 'index'],
      ['escape' => false, 'class' => 'nav-link', 'data-controller' => 'reports', 'data-action' => 'index']
    ) ?>
              </li>

              <li class="nav-item">
    <?= $this->Html->link(
      '<i class="nav-icon bi bi-question-circle-fill"></i><p>FAQ / Help Center</p>',
      ['controller' => 'Help', 'action' => 'index'],
      ['escape' => false, 'class' => 'nav-link', 'data-controller' => 'help', 'data-action' => 'index']
    ) ?>
              </li>
            <li class="nav-item">
    <?= $this->Html->link(
      '<i class="nav-icon bi bi-palette"></i><p>Profile Setting</p>',
      ['controller' => 'users', 'action' => 'profile'],
      ['escape' => false, 'class' => 'nav-link', 'data-controller' => 'users', 'data-action' => 'profile']
    ) ?>
  </li>
            
             
             
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
  // Get current controller and action from the page
  // This assumes they are passed to the view via the controller
  const currentController = '<?php echo strtolower($this->request->controller); ?>';
  const currentAction = '<?php echo strtolower($this->request->action); ?>';
  
  // Get all navigation links
  const navLinks = document.querySelectorAll('.nav-link[data-controller]');
  
  navLinks.forEach(link => {
    const linkController = link.getAttribute('data-controller').toLowerCase();
    const linkAction = link.getAttribute('data-action').toLowerCase();
    
    // Check if this link matches the current page
    if (linkController === currentController && linkAction === currentAction) {
      link.classList.add('active');
      // Also highlight the parent nav-item
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