<?php echo $this->element('navbar'); ?>
<?php echo $this->element('sidebar'); ?>

<main class="app-main">
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-6"><h3 class="mb-0">All Categories</h3></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item active">All Categories</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <div class="app-content">
    <div class="container-fluid">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Category List</h3>
            <div class="card-tools">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal" onclick="openCategoryModal();">Add Category</button>
          </div>
        </div>
        <div class="card-body table-responsive p-0">
          <table class="table table-hover text-nowrap">
            <thead>
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Active</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="categoriesTbody">
              <!-- Server-side fallback: render categories if available (useful after non-AJAX post/redirect) -->
              <?php if (!empty($categories)) : ?>
                <?php foreach ($categories as $c) :
                  $cat = isset($c['Category']) ? $c['Category'] : $c; ?>
                  <tr>
                    <td><?php echo h($cat['id']); ?></td>
                    <td><?php echo h($cat['name']); ?></td>
                    <td><?php echo h($cat['description']); ?></td>
                    <td class="category-status-cell" data-id="<?php echo (int)$cat['id']; ?>">
                      <span class="status-text"><?php echo ($cat['active'] == 1 || $cat['active'] === true) ? 'Active' : 'Inactive'; ?></span>
                    </td>
                    <td>
                      <div class="btn-group" role="group" aria-label="category-actions">
                        <button class="btn btn-outline-warning btn-sm" onclick="openCategoryModal(<?php echo (int)$cat['id']; ?>, <?php echo json_encode($cat['name']); ?>, <?php echo json_encode($cat['description']); ?>, <?php echo ($cat['active'] ? '1' : '0'); ?>);" title="Edit"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-outline-danger btn-sm" onclick="deleteCategory(<?php echo (int)$cat['id']; ?>);" title="Delete"><i class="bi bi-trash"></i></button>
                        <button class="btn btn-outline-<?php echo ($cat['active'] ? 'danger' : 'success'); ?> btn-sm" onclick="toggleCategoryStatus(<?php echo (int)$cat['id']; ?>, this);" title="<?php echo ($cat['active'] ? 'Deactivate' : 'Activate'); ?>"><i class="bi <?php echo ($cat['active'] ? 'bi-toggle-on' : 'bi-toggle-off'); ?>"></i></button>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <!-- Filled by AJAX -->
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Category Modal -->
  <div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="categoryModalTitle">Add Category</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
        </div>

        <?php echo $this->Form->create('Category', array('id' => 'categoryForm', 'url' => array('controller' => 'categories', 'action' => 'add'))); ?>
        <div class="modal-body bg-light">
          <div class="form-group">
            <?php echo $this->Form->input('name', array('label' => 'Name', 'class' => 'form-control', 'placeholder' => 'Category name')); ?>
          </div>
          <div class="form-group">
            <?php echo $this->Form->input('description', array('label' => 'Description', 'class' => 'form-control', 'type' => 'textarea', 'rows' => 3)); ?>
          </div>
          <div class="form-group form-check">
            <?php echo $this->Form->input('active', array('type' => 'checkbox', 'label' => 'Active', 'checked' => true)); ?>
          </div>
          <?php echo $this->Form->hidden('id', array('id' => 'categoryIdField')); ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Cancel</button>
          <?php echo $this->Form->submit('Save', array('class' => 'btn btn-primary', 'id' => 'categorySaveBtn')); ?>
        </div>
        <?php echo $this->Form->end(); ?>
      </div>
    </div>
  </div>

  <?php $this->start('script'); ?>
  <script>
  const categoriesApiUrl = '<?php echo $this->Html->url(array('controller'=>'categories','action'=>'index','_ext'=>'json')); ?>';
  const categoriesAddUrl = '<?php echo $this->Html->url(array('controller'=>'categories','action'=>'add','_ext'=>'json')); ?>';
  const categoriesEditBase = '<?php echo $this->Html->url(array('controller'=>'categories','action'=>'edit')); ?>';
  const categoriesDeleteBase = '<?php echo $this->Html->url(array('controller'=>'categories','action'=>'delete')); ?>';

  function loadCategories() {
    fetch(categoriesApiUrl)
      .then(res => res.json())
      .then(payload => {
        if (payload.success) renderCategories(payload.data);
      });
  }

  function renderCategories(categories) {
    const tbody = document.getElementById('categoriesTbody');
    tbody.innerHTML = '';
    categories.forEach(c => {
      const cat = c.Category || c;
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${cat.id}</td>
        <td>${escapeHtml(cat.name)}</td>
        <td>${escapeHtml(cat.description || '')}</td>
        <td class="category-status-cell" data-id="${cat.id}"><span class="status-text">${cat.active == 1 || cat.active === true ? 'Active' : 'Inactive'}</span></td>
        <td>
          <div class="btn-group" role="group" aria-label="category-actions">
            <button class="btn btn-outline-warning btn-sm" onclick="openCategoryModal(${cat.id}, ${JSON.stringify(cat.name)}, ${JSON.stringify(cat.description || '')}, ${cat.active});" title="Edit"><i class="bi bi-pencil"></i></button>
            <button class="btn btn-outline-danger btn-sm" onclick="deleteCategory(${cat.id});" title="Delete"><i class="bi bi-trash"></i></button>
            <button class="btn btn-outline-${cat.active == 1 ? 'danger' : 'success'} btn-sm" onclick="toggleCategoryStatus(${cat.id}, this);" title="${cat.active == 1 ? 'Deactivate' : 'Activate'}"><i class="bi ${cat.active == 1 ? 'bi-toggle-on' : 'bi-toggle-off'}"></i></button>
          </div>
        </td>
      `;
      tbody.appendChild(tr);
    });
  }

  function escapeHtml(text) {
    if (!text) return '';
    return String(text).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  }

  function openCategoryModal(id, name, description, active) {
    id = id || null;
    if (!id) {
      document.getElementById('categoryModalTitle').innerText = 'Add Category';
      document.getElementById('CategoryName').value = '';
      document.getElementById('CategoryDescription').value = '';
      document.getElementById('CategoryActive').checked = true;
      document.getElementById('categoryIdField').value = '';
      // set form action to add
      document.getElementById('categoryForm').action = '<?php echo $this->Html->url(array('controller'=>'categories','action'=>'add')); ?>';
    } else {
      document.getElementById('categoryModalTitle').innerText = 'Edit Category';
      document.getElementById('CategoryName').value = name;
      document.getElementById('CategoryDescription').value = description;
      document.getElementById('CategoryActive').checked = !!active;
      document.getElementById('categoryIdField').value = id;
      // set form action to edit
      document.getElementById('categoryForm').action = '<?php echo $this->Html->url(array('controller'=>'categories','action'=>'edit')); ?>/' + id;
    }

    // Show modal: support Bootstrap 4 (jQuery) and Bootstrap 5 (bootstrap.Modal)
    try {
      if (typeof $ !== 'undefined' && $.fn && $.fn.modal) {
        $('#categoryModal').modal('show');
      } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        var modalEl = document.getElementById('categoryModal');
        var modalInst = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modalInst.show();
      } else {
        // Fallback: make modal visible
        var modalEl = document.getElementById('categoryModal');
        modalEl.classList.add('show');
        modalEl.style.display = 'block';
      }
    } catch (err) {
      console.error('Error showing modal', err);
    }
  }

  // handle form submit via AJAX — initialize immediately (works if DOMContentLoaded already fired)
  (function initCategoryForm() {
    loadCategories();

    var form = document.getElementById('categoryForm');
    if (!form) return;

    form.addEventListener('submit', function(e) {
      e.preventDefault();
      const id = document.getElementById('categoryIdField').value;
      const nameEl = document.getElementById('CategoryName');
      const descEl = document.getElementById('CategoryDescription');
      const activeEl = document.getElementById('CategoryActive');
      const name = nameEl ? nameEl.value : '';
      const description = descEl ? descEl.value : '';
      const active = activeEl && activeEl.checked ? 1 : 0;

      const payload = {Category: {name: name, description: description, active: active}};

      if (!id) {
        fetch(categoriesAddUrl, {
          method: 'POST',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify(payload)
        }).then(r => r.json()).then(resp => {
          if (resp.success) {
            // hide modal (bootstrap 4/5 compatible)
            try { $('#categoryModal').modal('hide'); } catch(e){}
            try { var m = bootstrap.Modal.getInstance(document.getElementById('categoryModal')); if(m) m.hide(); } catch(e){}
            loadCategories();
          } else {
            alert('Save failed');
          }
        }).catch(()=>alert('Network error'));
      } else {
        fetch(categoriesEditBase + '/' + id + '.json', {
          method: 'POST',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify(payload)
        }).then(r => r.json()).then(resp => {
          if (resp.success) {
            try { $('#categoryModal').modal('hide'); } catch(e){}
            try { var m = bootstrap.Modal.getInstance(document.getElementById('categoryModal')); if(m) m.hide(); } catch(e){}
            loadCategories();
          } else {
            alert('Update failed');
          }
        }).catch(()=>alert('Network error'));
      }
    });
  })();

  // Toggle category status via AJAX
  function toggleCategoryStatus(id, btnEl) {
    if (!id) return;
    const url = '<?php echo $this->Html->url(array('controller'=>'categories','action'=>'toggle_status','_ext'=>'json')); ?>';
    fetch(url, {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({id: id})
    }).then(r => r.json()).then(resp => {
      if (resp && resp.success) {
        // update status text and button class
        const cell = document.querySelector('.category-status-cell[data-id="' + id + '"]');
        if (cell) {
          const txt = cell.querySelector('.status-text');
          if (txt) txt.innerText = resp.active == 1 ? 'Active' : 'Inactive';
        }
        if (btnEl) {
          // update outline color: active -> outline-danger (means "click to deactivate"), inactive -> outline-success (means "click to activate")
          btnEl.classList.remove('btn-outline-success', 'btn-outline-danger');
          if (resp.active == 1) {
            btnEl.classList.add('btn-outline-danger');
            btnEl.title = 'Deactivate';
            // set icon to toggle-on
            btnEl.innerHTML = '<i class="bi bi-toggle-on"></i>';
          } else {
            btnEl.classList.add('btn-outline-success');
            btnEl.title = 'Activate';
            btnEl.innerHTML = '<i class="bi bi-toggle-off"></i>';
          }
        }
      } else {
        alert((resp && resp.message) ? resp.message : 'Toggle failed');
      }
    }).catch(() => alert('Network error'));
  }

  function deleteCategory(id) {
    if (!confirm('Are you sure?')) return;
    fetch(categoriesDeleteBase + '/' + id + '.json', {method: 'POST'})
      .then(r => r.json())
      .then(resp => {
        if (resp.success) loadCategories(); else alert('Delete failed');
      });
  }
  </script>
  <?php $this->end(); ?>

</main>
