<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @package       app.View.Layouts
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

$cakeDescription = __d('cake_dev', 'CakePHP: the rapid development php framework');
$cakeVersion = __d('cake_dev', 'CakePHP %s', Configure::version())
?>
<!DOCTYPE html>
<html>
<head>
	<?php echo $this->Html->charset(); ?>
	<title>
		<?php echo $cakeDescription ?>:
		<?php echo $this->fetch('title'); ?>
	</title>
	<?php
		echo $this->Html->meta('icon');

		//echo $this->Html->css('cake.generic');

		echo $this->fetch('meta');
		echo $this->fetch('css');
		echo $this->fetch('script');
	?>
</head>
<body>
	<div id="container">
	
		<div id="content">

			<?php echo $this->Flash->render(); ?>

			<?php echo $this->fetch('content'); ?>

			<!-- Toast container for flash messages -->
			<div id="app-toast-container" aria-live="polite" aria-atomic="true"></div>

			<style>
			/* Toast styling: top center, green for success */
			#app-toast-container {
				position: fixed;
				top: 20px;
				left: 50%;
				transform: translateX(-50%);
				z-index: 1050;
				pointer-events: none;
			}
			.app-toast {
				display: inline-block;
				min-width: 240px;
				max-width: 720px;
				color: #fff;
				background: #28a745; /* default green */
				padding: 12px 18px;
				border-radius: 4px;
				box-shadow: 0 6px 18px rgba(0,0,0,0.12);
				font-size: 14px;
				margin-top: 8px;
				pointer-events: auto;
				opacity: 0;
				transition: opacity 0.25s ease, transform 0.25s ease;
			}
			.app-toast.show {
				opacity: 1;
				transform: translateY(0);
			}
			.app-toast.hide {
				opacity: 0;
				transform: translateY(-8px);
			}
			/* Error variant */
			.app-toast.error { background: #dc3545; }
			.app-toast.info { background: #17a2b8; }
			.app-toast.warning { background: #ffc107; color: #212529; }
			</style>

			<script>
			// Move CakePHP flash messages into a toast container and auto-hide them.
			document.addEventListener('DOMContentLoaded', function () {
				try {
					var container = document.getElementById('app-toast-container');
					if (!container) return;

					// CakePHP FlashHelper may output .message or .flash elements.
					var flashRoots = document.querySelectorAll('#content .message, #content .flash, #content .success, #content .error');
					flashRoots.forEach(function (el) {
						// Extract text
						var text = el.textContent.trim();
						if (!text) return;

						// Determine type from class names
						var type = 'success';
						var cls = el.className || '';
						if (/error|danger/i.test(cls)) type = 'error';
						if (/info/i.test(cls)) type = 'info';
						if (/warning/i.test(cls)) type = 'warning';

						// Create toast
						var toast = document.createElement('div');
						toast.className = 'app-toast ' + type;
						toast.setAttribute('role', 'alert');
						toast.innerText = text;
						container.appendChild(toast);

						// show
						setTimeout(function () { toast.classList.add('show'); }, 20);

						// hide after 5 seconds
						setTimeout(function () {
							toast.classList.remove('show');
							toast.classList.add('hide');
							setTimeout(function () { try { container.removeChild(toast); } catch(e){} }, 300);
						}, 5000);

						// Remove original flash node so it doesn't show twice
						try { el.parentNode && el.parentNode.removeChild(el); } catch(e){}
					});
				} catch (e) {
					console.error('Toast init error', e);
				}
			});
			</script>
		</div>
		
	</div>
	
</body>
</html>
