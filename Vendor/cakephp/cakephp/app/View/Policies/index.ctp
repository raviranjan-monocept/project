 <?php echo $this->element('navbar'); ?>
      <!--end::Header-->
      <!--begin::Sidebar-->
      <?php echo $this->element('sidebar'); ?>
      <!--end::Sidebar-->
      <!--begin::App Main-->
      <main class="app-main">
        <!--begin::App Content Header-->
        <div class="app-content-header">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
              <div class="col-sm-6"><h3 class="mb-0">All Policies</h3></div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="#">Home</a></li>
                  <li class="breadcrumb-item active" aria-current="page">All Policies</li>
                </ol>
              </div>
            </div>
            <!--end::Row-->
          </div>
          <!--end::Container-->
        </div>
        <div class="app-content">
          <!--begin::Container-->
          <div class="container-fluid">
            <!-- Info boxes -->
            <div class="row">
              <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                  <span class="info-box-icon text-bg-primary shadow-sm">
                    <i class="bi bi-gear-fill"></i>
                  </span>
                  <div class="info-box-content">
                    <span class="info-box-text">Total Policies</span>
                    <span class="info-box-number">
                       <h3><?php echo $stats['total']; ?></h3>
                    </span>
                  </div>
                  <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
              </div>
              <!-- /.col -->
              <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                  <span class="info-box-icon text-bg-danger shadow-sm">
                    <i class="bi bi-hand-thumbs-up-fill"></i>
                  </span>
                  <div class="info-box-content">
                    <span class="info-box-text">Active Policies</span>
                    <span class="info-box-number"><h3><?php echo $stats['active']; ?></h3>
</span>
                  </div>
                  <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
              </div>
              <!-- /.col -->
              <!-- fix for small devices only -->
              <!-- <div class="clearfix hidden-md-up"></div> -->
              <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                  <span class="info-box-icon text-bg-success shadow-sm">
                    <i class="bi bi-cart-fill"></i>
                  </span>
                  <div class="info-box-content">
                    <span class="info-box-text"> Draft Policies</span>
                    <span class="info-box-number"> <h3><?php echo $stats['draft']; ?></h3>
</span>
                  </div>
                  <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
              </div>
              <!-- /.col -->
              <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                  <span class="info-box-icon text-bg-warning shadow-sm">
                    <i class="bi bi-people-fill"></i>
                  </span>
                  <div class="info-box-content">
                    <span class="info-box-text">Archived Policies</span>
                    <span class="info-box-number"> <h3><?php echo $stats['archived']; ?></h3>
</span>
                  </div>
                  <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
              </div>
              <!-- /.col -->
            </div>
              <!-- Administrator List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                          <h3 class="card-title">Policy List</h3>
                          <div class="card-tools">
                            <button class="btn btn-primary btn-sm" type="button" onclick="openPolicyModal();"><i class="fas fa-plus"></i> Add Policy</button>
                          </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover text-nowrap">
                                <thead>
                                    <tr>
                                          <th>ID</th>
                                          <th>Title</th>
                                          <th>Category</th>
                                          <th>Description</th>
                                          <th>Status</th>
                                          <th>Created On</th>
                                          <th>Actions</th>
                                        </tr>
                                </thead>
                                 <tbody>
                            <?php 
                              // Build category map (id => name) from $categories if available
                              $categoryMap = array();
                              if (!empty($categories)) {
                                // If $categories is already an associative id => name map (returned by _getCategories), use it directly
                                $first = reset($categories);
                                if (!is_array($first) && !is_object($first)) {
                                  $categoryMap = $categories;
                                } else {
                                  foreach ($categories as $c) {
                                    $cat = isset($c['Category']) ? $c['Category'] : $c;
                                    if (isset($cat['id'])) $categoryMap[$cat['id']] = $cat['name'];
                                  }
                                }
                              }
                            ?>
                            <?php foreach ($policies as $policy): ?>
                                <tr>
                                    <td><?php echo h($policy['Policy']['id']); ?></td>
                                    <td>
                                        <strong>
                                            <?php echo $this->Html->link(
                                                h($policy['Policy']['title']),
                                                array('action' => 'view', $policy['Policy']['id'])
                                            ); ?>
                                        </strong>
                                    </td>
                                <td>
                                  <?php
                                    $catName = '';
                                    if (!empty($policy['Category']['name'])) {
                                      $catName = h($policy['Category']['name']);
                                    } elseif (!empty($policy['Policy']['category_id']) && isset($categoryMap[$policy['Policy']['category_id']])) {
                                      $catName = h($categoryMap[$policy['Policy']['category_id']]);
                                    }
                                    echo $catName;
                                  ?>
                                </td>
                                    <td>
                                        <?php 
                                            $desc = h($policy['Policy']['description']);
                                            echo strlen($desc) > 80 ? substr($desc, 0, 80) . '...' : $desc;
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                            $statusClass = '';
                                            switch($policy['Policy']['status']) {
                                                case 'active':
                                                    $statusClass = 'success';
                                                    break;
                                                case 'draft':
                                                    $statusClass = 'warning';
                                                    break;
                                                case 'archived':
                                                    $statusClass = 'default';
                                                    break;
                                            }
                                        ?>
                                        <span class="label label-<?php echo $statusClass; ?>">
                                            <?php echo strtoupper(h($policy['Policy']['status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo $this->Time->format('M d, Y h:i A', $policy['Policy']['created']); ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group" aria-label="policy-actions">
                                          <?php
                                          echo $this->Html->link(
                                            '<i class="bi bi-eye"></i>',
                                            array('action' => 'view', $policy['Policy']['id']),
                                            array('class' => 'btn btn-outline-primary btn-sm', 'escape' => false, 'title' => 'View')
                                          );

                                          echo ' ' . $this->Html->link(
                                            '<i class="bi bi-pencil"></i>',
                                            array('action' => 'edit', $policy['Policy']['id']),
                                            array('class' => 'btn btn-outline-warning btn-sm', 'escape' => false, 'title' => 'Edit')
                                          );

                                          echo ' ' . $this->Form->postLink(
                                            '<i class="bi bi-trash"></i>',
                                            array('action' => 'delete', $policy['Policy']['id']),
                                            array('class' => 'btn btn-outline-danger btn-sm', 'escape' => false, 'title' => 'Delete'),
                                            __('Are you sure you want to delete policy: %s?', $policy['Policy']['title'])
                                          );
                                          ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                            </table>
                        </div>
                
                    </div>
         
                </div>
            </div>
            <!-- Add Policy Modal -->
            <div class="modal fade" id="policyModal" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">Add Policy</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <?php
                    // Build category options if available
                    $policyCatOptions = array();
                    if (!empty($categories)) {
                      foreach ($categories as $c) {
                        $cat = isset($c['Category']) ? $c['Category'] : $c;
                        $policyCatOptions[$cat['id']] = $cat['name'];
                      }
                    }
                    echo $this->Form->create('Policy', array('id' => 'policyForm', 'url' => array('controller' => 'policies', 'action' => 'add')));
                  ?>
                  <div class="modal-body">
                    <div class="row mb-3">
                      <div class="col-md-6">
                        <?php echo $this->Form->input('category_id', array('type' => 'select', 'label' => 'Category', 'options' => $policyCatOptions, 'empty' => '-- Select Category --', 'class' => 'form-control')); ?>
                      </div>
                      <div class="col-md-6">
                        <?php echo $this->Form->input('title', array('label' => 'Policy Title', 'class' => 'form-control', 'placeholder' => 'e.g. Health Plus Gold')); ?>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="col-md-6">
                        <?php echo $this->Form->input('policy_no', array('label' => 'Policy Number', 'class' => 'form-control', 'placeholder' => 'e.g. POL-2025-0001')); ?>
                      </div>
                      <div class="col-md-6">
                        <?php echo $this->Form->input('sum_insured', array('label' => 'Sum Insured', 'class' => 'form-control', 'placeholder' => 'e.g. 500000')); ?>
                      </div>
                    </div>

                    <div class="mb-3">
                      <?php echo $this->Form->input('description', array('type' => 'textarea', 'label' => 'Description', 'rows' => 4, 'class' => 'form-control', 'placeholder' => 'Short summary of coverage, key terms, etc.')); ?>
                    </div>

                    <div class="row">
                      <div class="col-md-6">
                        <?php echo $this->Form->input('premium_amount', array('label' => 'Premium Amount', 'class' => 'form-control', 'placeholder' => 'e.g. 15000')); ?>
                      </div>
                      <div class="col-md-6">
                        <?php echo $this->Form->input('status', array('type' => 'select', 'label' => 'Status', 'options' => array('active' => 'Active', 'draft' => 'Draft', 'archived' => 'Archived'), 'class' => 'form-control')); ?>
                      </div>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <?php echo $this->Form->submit('Save', array('class' => 'btn btn-primary', 'id' => 'policySaveBtn')); ?>
                  </div>
                  <?php echo $this->Form->end(); ?>
                </div>
              </div>
            </div>
            </div>

        </div>
        <!--end::App Content-->
      </main>
      <!--end::App Main-->
  <?php echo $this->element('footer'); ?>
  <script>
    function openPolicyModal() {
      // Use absolute URL and show loading state
      var categoriesApiUrl = '<?php echo $this->Html->url(array('controller'=>'categories','action'=>'index','_ext'=>'json'), true); ?>';
      var select = document.querySelector('select[name="data[Policy][category_id]"]');
      if (select) {
        select.innerHTML = '<option value="">Loading categories...</option>';
      }

      fetch(categoriesApiUrl, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
        .then(function(r){
          if (!r.ok) throw new Error('Network response was not ok: ' + r.status);
          return r.json();
        })
        .then(function(payload){
          console.log('categories payload', payload);
          var select = document.querySelector('select[name="data[Policy][category_id]"]');
          if (!select) return;
          if (payload && payload.success && Array.isArray(payload.data) && payload.data.length) {
            select.innerHTML = '<option value="">-- Select Category --</option>';
            payload.data.forEach(function(c){
              var cat = c.Category || c;
              var opt = document.createElement('option');
              opt.value = cat.id;
              opt.text = cat.name;
              select.appendChild(opt);
            });
          } else {
            select.innerHTML = '<option value="">No categories found</option>';
          }
        }).catch(function(err){
          console.error('Failed to load categories', err);
          var select = document.querySelector('select[name="data[Policy][category_id]"]');
          if (select) select.innerHTML = '<option value="">Failed to load categories</option>';
        }).finally(function(){
          try {
            if (typeof $ !== 'undefined' && $.fn && $.fn.modal) {
              $('#policyModal').modal('show');
            } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
              var modalEl = document.getElementById('policyModal');
              var modalInst = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
              modalInst.show();
            } else {
              var modalEl = document.getElementById('policyModal');
              modalEl.classList.add('show');
              modalEl.style.display = 'block';
            }
          } catch (err) { console.error(err); }
        });
    }

    (function initPolicyForm() {
      var form = document.getElementById('policyForm');
      if (!form) return;
      form.addEventListener('submit', function(e) {
        e.preventDefault();
        var payload = { Policy: {} };
        var elems = form.querySelectorAll('input, textarea, select');
        elems.forEach(function(el) {
          if (!el.name) return;
          var m = el.name.match(/^data\[Policy\]\[(.+)\]$/);
          if (m) {
            var key = m[1];
            if (el.type === 'checkbox') payload.Policy[key] = el.checked ? 1 : 0;
            else payload.Policy[key] = el.value;
          }
        });

        var url = '<?php echo $this->Html->url(array('controller'=>'policies','action'=>'add','_ext'=>'json')); ?>';
        fetch(url, {
          method: 'POST',
          credentials: 'same-origin',
          headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
          body: JSON.stringify(payload)
        }).then(function(r){ return r.json(); }).then(function(resp){
          if (resp && resp.success) {
            try { $('#policyModal').modal('hide'); } catch(e){}
            try { var m = bootstrap.Modal.getInstance(document.getElementById('policyModal')); if (m) m.hide(); } catch(e){}
            window.location.reload();
          } else {
            alert((resp && resp.message) ? resp.message : 'Save failed');
          }
        }).catch(function(){ alert('Network error'); });
      });
    })();
  </script>