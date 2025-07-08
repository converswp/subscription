<?php
/**
 * Admin Go Pro Template
 */
?>

<?php if ( class_exists('Sdevs_Wc_Subscription_Pro') ) : ?>
	<div class="notice notice-info" style="margin:40px auto;max-width:700px;text-align:center;font-size:1.2em;">
		<?php esc_html_e( 'Pro is already active.', 'wp_subscription' ); ?>
	</div>
<?php else : ?>
	<div class="wrap wpsubscription-go-pro" style="max-width:900px;margin:40px auto 0 auto;">
		<div class="wpsubscription-go-pro-card" style="background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,0.06);padding:40px 32px 32px 32px;">
			<h1 style="margin-bottom:0.5em;"><?php esc_html_e( 'Upgrade to WPSubscription Pro', 'wp_subscription' ); ?></h1>
			<p style="font-size:1.12em;max-width:600px;line-height:1.6;">
				<?php esc_html_e( 'Unlock the full power of subscriptions for WooCommerce. Get advanced features, priority support, and more ways to grow your recurring revenue.', 'wp_subscription' ); ?>
			</p>
			<table class="wpsubscription-compare-table" style="width:100%;margin:32px 0 40px 0;border-collapse:separate;border-spacing:0;box-shadow:0 1px 4px rgba(0,0,0,0.04);background:#fafbfc;border-radius:8px;overflow:hidden;">
				<thead>
					<tr style="background:#f8f9fa;">
						<th style="padding:18px 12px 18px 24px;font-size:1.08em;text-align:left;border:none;"></th>
						<th style="padding:18px 12px;font-size:1.08em;text-align:center;border:none;"><?php esc_html_e( 'Free', 'wp_subscription' ); ?></th>
						<th style="padding:18px 12px;font-size:1.08em;text-align:center;border:none;color:#7f54b3;"><?php esc_html_e( 'Pro', 'wp_subscription' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td style="padding:16px 12px 16px 24px;"><?php esc_html_e( 'Simple subscription products', 'wp_subscription' ); ?></td>
						<td style="text-align:center;">✔️</td>
						<td style="text-align:center;">✔️</td>
					</tr>
					<tr style="background:#f6f7f7;">
						<td style="padding:16px 12px 16px 24px;"><?php esc_html_e( 'Automated recurring billing', 'wp_subscription' ); ?></td>
						<td style="text-align:center;">✔️</td>
						<td style="text-align:center;">✔️</td>
					</tr>
					<tr>
						<td style="padding:16px 12px 16px 24px;"><?php esc_html_e( 'Multiple payment gateways', 'wp_subscription' ); ?></td>
						<td style="text-align:center;">✔️</td>
						<td style="text-align:center;">✔️</td>
					</tr>
					<tr style="background:#f6f7f7;">
						<td style="padding:16px 12px 16px 24px;"><?php esc_html_e( 'Customer self-service portal', 'wp_subscription' ); ?></td>
						<td style="text-align:center;">✔️</td>
						<td style="text-align:center;">✔️</td>
					</tr>
					<tr>
						<td style="padding:16px 12px 16px 24px;"><?php esc_html_e( 'Priority support', 'wp_subscription' ); ?></td>
						<td style="text-align:center;">—</td>
						<td style="text-align:center;">✔️</td>
					</tr>
					<tr style="background:#f6f7f7;">
						<td style="padding:16px 12px 16px 24px;"><?php esc_html_e( 'Advanced reporting & analytics', 'wp_subscription' ); ?></td>
						<td style="text-align:center;">—</td>
						<td style="text-align:center;">✔️</td>
					</tr>
					<tr>
						<td style="padding:16px 12px 16px 24px;"><?php esc_html_e( 'Variable product support', 'wp_subscription' ); ?></td>
						<td style="text-align:center;">—</td>
						<td style="text-align:center;font-weight:600;color:#43a047;">✔️</td>
					</tr>
				</tbody>
			</table>
			<div style="text-align:center;margin-top:24px;">
				<a href="https://wpsubscription.co/" target="_blank" class="button button-primary button-hero" style="font-size:1.2em;padding:16px 40px 16px 40px;background:#7f54b3;border:none;box-shadow:0 2px 8px rgba(127,84,179,0.10);">
					<?php esc_html_e( 'Upgrade to Pro', 'wp_subscription' ); ?>
				</a>
			</div>
		</div>
	</div>
<?php endif; ?> 