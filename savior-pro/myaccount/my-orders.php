<?php
/**
 * My Orders - Deprecated
 *
 * @deprecated 2.6.0 this template file is no longer used. My Account shortcode uses orders.php.
 * @package WooCommerce\Templates
 */

defined( 'ABSPATH' ) || exit;

$my_orders_columns = apply_filters(
	'woocommerce_my_account_my_orders_columns',
	array(
		'order-number'  => esc_html__( 'Invoice', 'woocommerce' ),
		'product-title'  => esc_html__( 'Description', 'woocommerce' ),
		'order-date'    => esc_html__( 'Date', 'woocommerce' ),
		'order-total'   => esc_html__( 'Price', 'woocommerce' ),
		'order-status'  => esc_html__( 'Status', 'woocommerce' ),
		'order-actions' => '&nbsp;',
	)
);

$customer_orders = get_posts(
	apply_filters(
		'woocommerce_my_account_my_orders_query',
		array(
			'numberposts' => $order_count,
			'meta_key'    => '_customer_user',
			'meta_value'  => get_current_user_id(),
			'post_type'   => wc_get_order_types( 'view-orders' ),
			'post_status' => array_keys( wc_get_order_statuses() ),
		)
	)
);

if ( $customer_orders ) : ?>

<h2><?php echo apply_filters( 'woocommerce_my_account_my_orders_title', esc_html__( 'Recent orders', 'woocommerce' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h2>

<table class="shop_table shop_table_responsive my_account_orders">

	<thead>
		<tr>
			<?php foreach ( $my_orders_columns as $column_id => $column_name ) : ?>
			<th class="<?php echo esc_attr( $column_id ); ?>"><span class="nobr"><?php echo esc_html( $column_name ); ?></span></th>
			<?php endforeach; ?>
		</tr>
	</thead>

	<tbody>
		<?php
		foreach ( $customer_orders as $customer_order ) :
		$order      = wc_get_order( $customer_order ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$item_count = $order->get_item_count();
		$order_id  = $order->get_id(); 
		?>
		<tr class="order">
			<?php foreach ( $my_orders_columns as $column_id => $column_name ) : ?>
			<td class="<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>">
				<?php if ( has_action( 'woocommerce_my_account_my_orders_column_' . $column_id ) ) : ?>
				<?php do_action( 'woocommerce_my_account_my_orders_column_' . $column_id, $order ); ?>

				<?php elseif ( 'order-number' === $column_id ) : ?>
				<?php echo _x( '#', 'hash before order number', 'woocommerce' ) . $order->get_order_number(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

				<?php elseif ( 'product-title' === $column_id ) : ?>
				<?php foreach($order->get_items() as $item) { $product_name = $item['name']; }  echo $product_name; ?>

				<?php elseif ( 'order-date' === $column_id ) : ?>
				<time datetime="<?php echo esc_attr( $order->get_date_created()->date( 'c' ) ); ?>"><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></time>

				<?php elseif ( 'order-status' === $column_id ) : ?>
				<?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?>

				<?php elseif ( 'order-total' === $column_id ) : ?>
				<?php
				/* translators: 1: formatted order total 2: total order items */
				printf( _n( '%1$s', '%1$s', $item_count, 'woocommerce' ), $order->get_formatted_order_total(), $item_count ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>

				<?php elseif ( 'order-actions' === $column_id ) : ?>
				<?php
				$actions = wc_get_account_orders_actions( $order );

				if ( ! empty( $actions ) ) {
					foreach ( $actions as $key => $action ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
						echo '<a href="' . esc_url( $action['url'] ) . '" class="button ' . sanitize_html_class( $key ) . '">View Invoice</a>';
						// echo  '<a href="javascript:void(0)" class="button dt-download-icon download' . sanitize_html_class( $key ) . '" id="download_invoice" data-orderId="'.$order_id.'"><i class="elementor-icons-manager__tab__item__icon fa fa-file-pdf-o"></i></a>';
					}
				}
				?>
				<?php endif; ?>
			</td>
			<?php endforeach; ?>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php else : echo '<div class="custom-order-not-found">
							<h2 class="elementor-heading-title elementor-size-default">No orders yet.</h2>
							<h3 class="elementor-heading-title elementor-size-default">Your orders will appear here.</h3>
				   </div>'; 
endif; ?>

<!-- <div id="test_data"></div> -->