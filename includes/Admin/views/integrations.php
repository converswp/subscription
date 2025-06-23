<?php
/**
 * Payment Gateways Admin Page
 *
 * Displays available payment gateway options as cards.
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>

<div class="wrap">
	<!-- The Irony -->
<!-- 	<div style="max-width: 600px; margin: 50px auto; padding: 20px; font-family: Georgia, serif; font-size: 1.2em; line-height: 1.6; color: #333; text-align: center;">
		We automate to move faster,<br>
		optimize to do more—<br>
		but somewhere between the clicks and code,<br>
		we forget how to wonder.<br>
		The machines learn,<br>
		and we forget to ask why.
	</div> -->

	<div class="wp-subscription-admin-content" style="max-width:1240px;margin:32px auto 0 auto; background:wheat; padding: 20px;">
		<h1>Incomplete. Demo Page</h1>
	</div>

	<br/>
	<br/>

	<div class="wp-subscription-admin-content" style="max-width:1240px;margin:32px auto 0 auto">
		<h2><?php esc_html_e( 'Payment Gateways', 'wp_subscription' ); ?></h2>
		
		<p style="font-size:14px;line-height:1.7;margin:0 0 24px 0;">
			<?php esc_html_e( 'Configure your store\'s payment gateways for subscription products. Enable, disable, and manage available payment methods that support recurring billing.', 'wp_subscription' ); ?>
		</p>

		<!-- Payment Gateway Cards -->
		<div class="wp-subscription-payment-gateways" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:24px;margin-bottom:32px;">
			<?php
			// Example payment gateways (for demonstration)
			$subscription_gateways = array(
				'stripe' => array(
					'title'              => 'Stripe',
					'description'        => 'Process subscription payments securely with Stripe.',
					'icon'               => '💳',
					'is_connected'       => true,
					'supports_recurring' => true,
				),
				'paypal' => array(
					'title'              => 'PayPal',
					'description'        => 'Accept subscription payments via PayPal.',
					'icon'               => '💰',
					'is_connected'       => false,
					'supports_recurring' => true,
				),
				'manual' => array(
					'title'              => 'Manual Renewal',
					'description'        => 'Subscriptions that require manual renewal by the customer.',
					'icon'               => '🔄',
					'is_connected'       => true,
					'supports_recurring' => false,
				),
			);

			// Display each gateway as a card
			foreach ( $subscription_gateways as $id => $gateway ) :
				$status_class   = $gateway['is_connected'] ? 'active' : 'inactive';
				$supports_class = $gateway['supports_recurring'] ? 'supports-recurring' : '';
				?>
				<div class="wp-subscription-admin-box payment-gateway-card <?php echo esc_attr( $status_class ); ?> <?php echo esc_attr( $supports_class ); ?>" 
					style="background:#f4f7fa;border-radius:8px;padding:20px;box-shadow:0 2px 8px #e0e7ef;">
					<div style="display:flex;align-items:center;gap:14px;margin-bottom:16px;">
						<span style="font-size:1.8em;color:#2196f3;"><?php echo esc_html( $gateway['icon'] ); ?></span>
						<span style="font-size:1.2em;font-weight:600;"><?php echo esc_html( $gateway['title'] ); ?></span>
						
						<span style="margin-left:auto;font-size:0.85em;padding:4px 8px;border-radius:12px;<?php echo $gateway['is_connected'] ? 'background:#e3f2fd;color:#0d47a1;' : 'background:#ffebee;color:#b71c1c;'; ?>">
							<?php echo $gateway['is_connected'] ? esc_html__( 'Connected', 'wp_subscription' ) : esc_html__( 'Not Connected', 'wp_subscription' ); ?>
						</span>
					</div>
					
					<p style="font-size:14px;line-height:1.5;margin:0 0 16px 0;color:#555;">
						<?php echo esc_html( $gateway['description'] ); ?>
					</p>
					
					<?php if ( $gateway['supports_recurring'] ) : ?>
						<div style="background:#e8f5e9;color:#1b5e20;padding:6px 10px;border-radius:4px;font-size:13px;margin-bottom:16px;">
							<span style="display:flex;align-items:center;gap:6px;">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z" fill="#1b5e20"/>
								</svg>
								<?php esc_html_e( 'Supports automatic recurring billing', 'wp_subscription' ); ?>
							</span>
						</div>
					<?php else : ?>
						<div style="background:#fff3e0;color:#e65100;padding:6px 10px;border-radius:4px;font-size:13px;margin-bottom:16px;">
							<span style="display:flex;align-items:center;gap:6px;">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" fill="#e65100"/>
								</svg>
								<?php esc_html_e( 'Manual renewals only', 'wp_subscription' ); ?>
							</span>
						</div>
					<?php endif; ?>
					
					<div style="display:flex;gap:10px;">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=' . $id ) ); ?>" class="button button-primary" style="flex:1;text-align:center;">
							<?php esc_html_e( 'Configure', 'wp_subscription' ); ?>
						</a>
						<?php if ( ! $gateway['is_connected'] ) : ?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=' . $id ) ); ?>" class="button" style="flex:1;text-align:center;">
								<?php esc_html_e( 'Connect', 'wp_subscription' ); ?>
							</a>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<!-- Information box -->
		<div class="wp-subscription-admin-box" style="margin-bottom:24px;">
			<h3><?php esc_html_e( 'About Payment Gateways', 'wp_subscription' ); ?></h3>
			<p style="font-size:14px;line-height:1.7;margin:0 0 10px 0;">
				<?php esc_html_e( 'For subscription products to work properly, you need to use payment gateways that support recurring payments. Some payment methods only support manual renewals, which requires customers to manually pay for each renewal period.', 'wp_subscription' ); ?>
			</p>
			<ul style="font-size:14px;line-height:1.6;margin:0 0 0 18px;padding:0;list-style:disc;">
				<li><?php esc_html_e( 'Automatic recurring billing requires a compatible payment gateway', 'wp_subscription' ); ?></li>
				<li><?php esc_html_e( 'Manual renewal methods work with any payment gateway', 'wp_subscription' ); ?></li>
				<li><?php esc_html_e( 'Some gateways may require additional configuration for subscriptions', 'wp_subscription' ); ?></li>
			</ul>
		</div>

		<!-- Documentation box -->
		<div class="wp-subscription-support-resources" style="display:grid;grid-template-columns:repeat(2,1fr);gap:24px;margin-bottom:24px;">
			<div class="wp-subscription-admin-box">
				<h3><?php esc_html_e( 'Payment Gateway Documentation', 'wp_subscription' ); ?></h3>
				<p style="font-size:14px;margin:0 0 8px 0;">
					<?php esc_html_e( 'Learn how to set up and configure payment gateways for subscription products.', 'wp_subscription' ); ?>
				</p>
				<a href="https://docs.converslabs.com/en" target="_blank" class="button button-small" style="font-size:13px;padding:5px 14px;">
					<?php esc_html_e( 'View Documentation', 'wp_subscription' ); ?>
				</a>
			</div>
			<div class="wp-subscription-admin-box">
				<h3><?php esc_html_e( 'Need Help?', 'wp_subscription' ); ?></h3>
				<p style="font-size:14px;margin:0 0 8px 0;">
					<?php esc_html_e( 'If you\'re having trouble with a payment gateway, our support team can help.', 'wp_subscription' ); ?>
				</p>
				<a href="https://wpsubscription.co/contact" target="_blank" class="button button-small" style="font-size:13px;padding:5px 14px;">
					<?php esc_html_e( 'Get Support', 'wp_subscription' ); ?>
				</a>
			</div>
		</div>
	</div>

	<div style="text-align:center;margin:38px 0 0 0;font-size:14px;color:#888;">
		<?php esc_html_e( 'Made with', 'wp_subscription' ); ?> <span style="color:#e25555;font-size:1.1em;">♥</span> <?php esc_html_e( 'by the WP Subscription Team', 'wp_subscription' ); ?>
		<div style="margin-top:6px;">
			<a href="https://wpsubscription.co/contact" target="_blank" style="color:#2563eb;text-decoration:none;"><?php esc_html_e( 'Support', 'wp_subscription' ); ?></a>
			&nbsp;/&nbsp;
			<a href="https://docs.converslabs.com/en" target="_blank" style="color:#2563eb;text-decoration:none;"><?php esc_html_e( 'Documentation', 'wp_subscription' ); ?></a>
		</div>
	</div>
</div>
