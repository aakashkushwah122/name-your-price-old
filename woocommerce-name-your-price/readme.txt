=== WooCommerce Name Your Price ===

Contributors: Kathy Darling    
Requires at least: 4.4.0    
Tested up to: 6.3.0    
Stable tag: 3.5.13
License: GPLv3    
License URI: http://www.gnu.org/licenses/gpl-3.0.html    
WC requires at least: 4.0.0    
WC tested up to: 8.2.0   

Allow customers to set their own prices for WooCommerce products

== Upgrade Notice ==
WooCommerce 2.6.x is no longer supported. Do not upgrade unless you are using at least WooCommerce 3.0.

== Description ==

The WooCommerce Name Your Price extension lets you be flexible in what price you are willing to accept for selected products. You can use this extension to accept user-determined donations, gather pricing data or to take a new approach to selling products!  You can *suggest* a price to your customers and optionally enforce a minimum acceptable price, but otherwise this extension allows the customer to enter the price they are willing to pay.

[https://raw.githubusercontent.com/woothemes/woocommerce-name-your-price/master/screenshots/screenshot1.png]

== Installation ==

To install Name Your Price:

1. Download the extension from your WooCommerce.com My Account area.

2. In your WordPress admin, go to Plugins > Add New and then click on the "Upload" tab

3. Click the "Choose File" button, select the zip file you just downloaded to your computer and then click "Install Now"

4. After installation has completed you can activate the plugin right away or you can activate the 'WooCommerce Name Your Price' extension through the 'Plugins' menu in WordPress at any time

== How to Use ==

= How to Use With Simple Products = 

![Simple product admin view, shows a highlighted checkbox labeled "Name Your Price"](https://user-images.githubusercontent.com/507025/151900142-99bb965b-a2af-4fb2-b185-e652948c9464.png)

To enable flexible, user-determined pricing on any simple product:

1. Edit a product and look for the 'Name Your Price' checkbox in the Product Data metabox. Simple, subscription, mix and match, paywall, bundle, and composite products will support customers naming their own price. 

2. Tick the checkbox to allow users to set their own price for this product.  The suggested and minimum price fields will not be visible until this is checked. Note that this might not function properly if you have javascript disabled. 

3. Fill in the suggested and minimum prices as desired. The minimum price prevents products from being sold for less than you are willing to accept. To *not* display a suggested price, you can simply leave the suggested field blank. Similarly, to not enforce a minimum, simply leave the minimum field blank. 

4. Save the product.  When viewing the product on the front-end, the suggested and minimum prices will be displayed in place of the regular price and a text input will appear above the Add to Cart Button where the customer can enter what she is willing to pay.  

= How to Use With Variable Products = 

As of Name Your Price 2.0, you can now have name your price variations on variable or variable Subscriptions products. Within each variation look for a "Name Your Price" checkbox and follow the same rules as simple products for suggested and minimum prices for each variation.

![Variations admin: The variation 146 shows a highlighted checkbox labeled "Name Your Price"](https://user-images.githubusercontent.com/507025/151899635-31bfee8a-e4c6-4c69-ae3e-5a4f80bda41d.png)

On the front-end when a Name Your Price variation is selected, the Name Your Price price input will appear. 

![Frontend view of a variable product with a "Amount" attribute set to "other". Shows a text input before an add to cart button](https://user-images.githubusercontent.com/507025/151899472-965ea810-39e2-4fff-9287-526bd58f93d9.png)

== FAQ ==

= How do I change the markup? =

Similar to WooCommerce, the Name Your Price extension uses a small template part to generate the markup for the price input. For example, you can use your own price input template by placing a price-input.php file inside the /woocommerce/single-product folder of your theme. However, much of what makes the Name Your Price component function is in that template, so only edit this if you really know what you are doing.

= How can I move the markup? = 

The suggested price & minimum price are displayed in place of the regular price, which is attached to the WooCommerce 'woocommerce_single_product_summary' action hook, while the text input is attached to the 'woocommerce_before_add_to_cart_button' hook (for a simple product!). Following typical WordPress behavior for hooks, to change the location of any of these templates you must remove them from their default hook and add them to a new hook.  For example, to relocate the price input place the following in your theme's functions.php and be sure to adjust 'the_hook_you_want_to_add_to' with a real hook name.

**Name Your Price 2.0**

```
function nyp_move_price_input(){
	if( function_exists( 'WC_Name_Your_Price' ) ){
		$wc_name_your_price_display = WC_Name_Your_Price()->display;
		remove_action( 'woocommerce_before_add_to_cart_button', array( $wc_name_your_price_display, 'display_price_input') );
		add_action( 'the_hook_you_want_to_add_to', array( $wc_name_your_price_display, 'display_price_input' ) );
	}
}
add_action( 'woocommerce_before_single_product' , 'nyp_move_price_input' );
```

== Plugin Settings ==

Name Your Price has several strings that can be modified from the plugin's settings. Go to WooCommerce->Settings and and click on the "Name Your Price" tab. From here you can modify the add to cart button texts, the minimum, and suggested text strings. 

![plugin settings screenshot. Located at WooCommerce>Settings>Name Your Price there are inputs for plugin labels such as Minimum price, suggested price, etc](https://user-images.githubusercontent.com/507025/151899846-c052a46c-7276-4825-a22d-de36986c6aff.png)
