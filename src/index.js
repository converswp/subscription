import {
    ExperimentalOrderMeta,
    registerCheckoutFilters,
    TotalsItem,
} from '@woocommerce/blocks-checkout';
import { FormattedMonetaryAmount } from '@woocommerce/blocks-components';
import { __ } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';

import { getCurrencyFromPriceResponse } from '@woocommerce/price-format';
// import { useStoreCart } from "@woocommerce/base-context/hooks";

const modifyCartItemPrice = ( defaultValue, extensions, args, validation ) => {
	const { sdevs_subscription } = extensions;
	const { cartItem } = args;
	const { totals } = cartItem;
	
	if ( totals === undefined ) {
		return defaultValue;
	}
	if ( totals.line_total === '0' ) {
		return `<price/> Due Today`;
	}
	if ( sdevs_subscription && sdevs_subscription.type ) {
		// Check renewal_limit - handle string, number, null, undefined
		const renewalLimit = parseInt(sdevs_subscription.renewal_limit, 10);
		const renewalInfo = !isNaN(renewalLimit) && renewalLimit > 0 
			? ` x ${renewalLimit}` 
			: '';
		return `<price/> / ${
			sdevs_subscription.time && sdevs_subscription.time > 1
				? ' ' + sdevs_subscription.time + '-'
				: ''
		}${ sdevs_subscription.type }${renewalInfo}`;
	}
	return defaultValue;
};

const modifySubtotalPriceFormat = (
	defaultValue,
	extensions,
	args,
	validation
) => {
	const { sdevs_subscription } = extensions;
	const { sdevs_subscription: recurrings } = extensions;
	
	if ( sdevs_subscription && sdevs_subscription.type ) {
		// Check renewal_limit - handle string, number, null, undefined
		const renewalLimit = parseInt(sdevs_subscription.renewal_limit, 10);
		const renewalInfo = !isNaN(renewalLimit) && renewalLimit > 0 
			? ` x ${renewalLimit}` 
			: '';
		return `<price/> ${ __('Every', 'wp_subscription') } ${
			sdevs_subscription.time && sdevs_subscription.time > 1
				? ' ' + sdevs_subscription.time + '-'
				: ''}${ sdevs_subscription.type }${renewalInfo}`;
	}
	
	return defaultValue;
};

registerCheckoutFilters( 'sdevs-subscription', {
	cartItemPrice: modifyCartItemPrice,
	subtotalPriceFormat: modifySubtotalPriceFormat,
} );

const RecurringTotals = ( { cart, extensions } ) => {
	if ( Object.keys( extensions ).length === 0 ) {
		return;
	}
	const { cartTotals } = cart;
	const { sdevs_subscription: recurrings } = extensions;
	const currency = getCurrencyFromPriceResponse( cartTotals );
	if ( recurrings.length === 0 ) {
		return;
	}
	return (
		<TotalsItem
			className="wc-block-components-totals-footer-item"
			label={ __( 'Recurring totals', 'wp_subscription' ) }
			description={
				<div style={ { display: 'grid' } }>
					{ recurrings.map( ( recurring ) => (
						<div style={ { margin: '20px 0', float: 'right' } }>
							<div style={ { fontSize: '18px' } }>
								<FormattedMonetaryAmount
									currency={ currency }
									value={ parseInt( recurring.price, 10 ) }
								/>{ ' ' }
								/{ ' ' }
								{ recurring.time && recurring.time > 1
									? `${
											recurring.time +
											'-' +
											recurring.type
									  } `
									: recurring.type }
							</div>
							<small>{ recurring.description }</small>
							{ recurring.can_user_cancel === 'yes' && (
								<>
									<br />
									<small>
										You can cancel subscription at any time!{ ' ' }
									</small>
								</>
							) }
							{ recurring.renewal_limit > 0 && (
								<>
									<br />
									<small>
										This subscription will be built for { recurring.renewal_limit } times.
									</small>
								</>
							) }
						</div>
					) ) }
				</div>
			}
		></TotalsItem>
	);
};

const render = () => {
	return (
		<ExperimentalOrderMeta>
			<RecurringTotals />
		</ExperimentalOrderMeta>
	);
};

registerPlugin( 'sdevs-subscription', {
	render,
	scope: 'woocommerce-checkout',
} );
