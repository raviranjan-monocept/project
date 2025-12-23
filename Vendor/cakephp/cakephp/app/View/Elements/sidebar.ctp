 <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <!--begin::Sidebar Brand-->
        <div class="sidebar-brand">
          <!--begin::Brand Link-->
          <a href="./index.html" class="brand-link">
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
      ['escape' => false, 'class' => 'nav-link']
    ) ?>
  </li>
  

          <!-- Admin Management -->
  <li class="nav-item">
    <?= $this->Html->link(
      '<i class="nav-icon bi bi-palette"></i><p>Admin Management</p>',
      ['controller' => 'Admins', 'action' => 'index'],
      ['escape' => false, 'class' => 'nav-link']
    ) ?>
  </li>

                     <!-- Policy Management -->
  <li class="nav-item">
    <?= $this->Html->link(
      '<i class="nav-icon bi bi-palette"></i><p>Policy Management</p>',
      ['controller' => 'Policies', 'action' => 'index'],
      ['escape' => false, 'class' => 'nav-link']
    ) ?>
  </li>
              
              <li class="nav-item">
                <a href="./generate/theme.html" class="nav-link">
                  <i class="nav-icon bi bi-palette"></i>
                  <p>Claims & Approvals</p>
                </a>
              </li>
 
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-box-seam-fill"></i>
                  <p>
                    All Categories
                  </p>
                </a>
                
              </li>

              <li class="nav-item">
                <a href="./docs/introduction.html" class="nav-link">
                  <i class="nav-icon bi bi-download"></i>
                  <p>Payments & Billing</p>
                </a>
              </li>
             <li class="nav-item">
                <a href="./docs/introduction.html" class="nav-link">
                  <i class="nav-icon bi bi-download"></i>
                  <p>Reports & Analytics</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="./docs/faq.html" class="nav-link">
                  <i class="nav-icon bi bi-question-circle-fill"></i>
                  <p>FAQ / Help Center</p>
                </a>
              </li>
            <li class="nav-item">
    <?= $this->Html->link(
      '<i class="nav-icon bi bi-palette"></i><p>Profile Setting</p>',
      ['controller' => 'users', 'action' => 'profile'],
      ['escape' => false, 'class' => 'nav-link']
    ) ?>
  </li>
            
             
             
            </ul>
            <!--end::Sidebar Menu-->
          </nav>
        </div>
        <!--end::Sidebar Wrapper-->
      </aside>