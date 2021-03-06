<?php

$installer = $this;
$installer->startSetup();

$content = <<<EOT
<div class="shopby">
<ul class="shop-by">
<li><span class="title">Popular Brand</span></li>
<li><a href="#">Mamy Poko</a></li>
<li><a href="#">Pampers</a></li>
<li><a href="#">Goon</a></li>
<li><a href="#">Nutrilon</a></li>
<li><a href="#">Bebelac</a></li>
<li><a href="#">Morinaga</a></li>
<li><a href="#">Nan</a></li>
<li><a href="#">Sebamed</a></li>
<li><a href="#">Cussons</a></li>
<li><a href="#">Zwitsal</a></li>
<li><a href="#">Philips Avent</a></li>
<li><a href="#">Dr. Browns</a></li>
<li><a href="#">Chicco</a></li>
<li><a href="#">Merries</a></li>
<li><a href="#">Maoo</a></li>
<li><a class="link-seemore" href="#">See more</a></li>
</ul>
<ul class="shop-by">
<li><span class="title">Gift Ideas</span></li>
<li><a href="#">Checklist For New Moms</a></li>
<li><a href="#">Hospital Bag Checklist</a></li>
<li><a href="#">Birthday Gift Ideas</a></li>
<li><a href="#">Baby Shower Gift Ideas</a></li>
<li><a class="link-seemore" href="#">See more</a></li>
</ul>
<ul class="shop-by" style="background: none;">
<li><span class="title">Product Highlights</span></li>
<li><a href="#">Baby Monitor</a></li>
<li><a href="#">Stroller</a></li>
<li><a href="#">Baby Box</a></li>
<li><a class="link-seemore" href="#">See more</a></li>
</ul>
</div>
EOT;

$staticBlock = array(
    'title' => 'Shop by',
    'identifier' => 'shopby',
    'content' => $content,
    'is_active' => 1,
    'stores' => array(0)
);

$staticBlockModel = Mage::getModel('cms/block')->load('shopby');

if($id = $staticBlockModel->getBlockId()){
    $staticBlockModel->setData($staticBlock)->setBlockId($id)->save();
}else{
    $staticBlockModel->setData($staticBlock)->save();
}

$cmsPage = Mage::getModel('cms/page')->load('home', 'identifier');

$pageContent =<<<EOF
<div class="homepage">
<div class="homepage-top">
<div class="homepage-top-block left">{{block type="cms/block" block_id="shopby"}}</div>
<div class="homepage-top-block middle" id="homepage-middle">
<div class="slider-wrapper">
{{block type="awislider/block" id="homepage"}}
</div>
<div class="advertise">
<ul>
<li>{{block type="cms/block" block_id="small_banner_left"}}</li>
<li>{{block type="cms/block" block_id="small_banner_right"}}</li>
</ul>
</div>
</div>
<div class="homepage-top-block right">{{block type="cms/block" block_id="banner_right"}}</div>
<div class="clear">&nbsp;</div>
</div>
<div class="homepage-middle">
<div class="homepage-product onsale">
<h2 class="title">Products on Sale!!</h2>
<a class="link-viewmore-product" href="#">View more products</a></div>
{{block type="awfeatured/block" id="product_sale"}}
<div class="homepage-product newarrival">
<h2 class="title">New Arrival</h2>
<a class="link-viewmore-product" href="#">View more products</a></div>
{{block type="awfeatured/block" id="new_arrival"}}
<div class="homepage-product mostpopular">
<h2 class="title">Most Popular</h2>
<a class="link-viewmore-product" href="#">View more products</a></div>
{{block type="awfeatured/block" id="most_popular"}}
</div>
<div class="homepage-bottom">
{{block type="cms/block" block_id="block_home"}}
{{block type="core/template" template="page/template/testimonial.phtml" identifier="testimonial_"}}
{{block type="cms/block" block_id="media_partners"}}
<div class="clear">&nbsp;</div>
</div>
{{block type="core/template" template="page/template/bottommenu.phtml"}}
</div>
<script type="text/javascript">// <![CDATA[
jQuery(window).load(function() {
jQuery('#slides').slidesjs({
width: 376,
height: 120,
navigation: false,
pagination: false
});
});
// ]]></script>
EOF;


if($cmsPage->getId()){
	$cmsPage->setTitle('Home page')->setIdentifier('home');
}

$cmsPage->setStores(0)
		->setContent($pageContent)
		->setIsActive(1)
		->setRootTemplate('one_column')
		->save();

$content = <<<EOT
<div class="submenu popular-brand">
<span class="title">Popular Brands</span>
<ul>
<li><a href="#">MamyPoko</a></li>
<li><a href="#">Pampers</a></li>
<li><a href="#">Merries</a></li>
<li><a href="#">Nepia</a></li>
<li><a href="#">Huggies</a></li>
<li><a href="#">Ruparooz</a></li>
<li><a href="#">Okiedog</a></li>
</ul>
</div>
EOT;

$staticBlock = array(
    'title' => 'Popular Brands',
    'identifier' => 'popular-brands',
    'content' => $content,
    'is_active' => 1,
    'stores' => array(0)
);

$staticBlockModel = Mage::getModel('cms/block')->load('popular-brands');

if($id = $staticBlockModel->getBlockId()){
    $staticBlockModel->setData($staticBlock)->setBlockId($id)->save();
}else{
    $staticBlockModel->setData($staticBlock)->save();
}

$content = <<<EOT
<div class="submenu our-choice">
<span class="title">Our Choice</span>
<ul>
<li><a href="#">MamyPoko</a></li>
<li><a href="#">Pampers</a></li>
<li><a href="#">Merries</a></li>
<li><a href="#">Nepia</a></li>
<li><a href="#">Huggies</a></li>
<li><a href="#">Ruparooz</a></li>
<li><a href="#">Okiedog</a></li>
</ul>
</div>
EOT;

$staticBlock = array(
    'title' => 'Our Choice',
    'identifier' => 'our-choice',
    'content' => $content,
    'is_active' => 1,
    'stores' => array(0)
);

$staticBlockModel = Mage::getModel('cms/block')->load('our-choice');

if($id = $staticBlockModel->getBlockId()){
    $staticBlockModel->setData($staticBlock)->setBlockId($id)->save();
}else{
    $staticBlockModel->setData($staticBlock)->save();
}

$content = <<<EOT
<div class="keypoint">
<ul>
<li class="free-shipping"><span class="title">Free Shipping</span>
<p>For order under Rp. 200.000,-</p>
</li>
<li class="cod"><span class="title">COD Payment</span>
<p>For Jadetabek area only</p>
</li>
<li class="return"><span class="title">Free Shipping</span>
<p>For Jadetabek area only</p>
</li>
</ul>
</div>
EOT;

$staticBlock = array(
    'title' => 'Keypoint',
    'identifier' => 'keypoint',
    'content' => $content,
    'is_active' => 1,
    'stores' => array(0)
);

$staticBlockModel = Mage::getModel('cms/block')->load('keypoint');

if($id = $staticBlockModel->getBlockId()){
    $staticBlockModel->setData($staticBlock)->setBlockId($id)->save();
}else{
    $staticBlockModel->setData($staticBlock)->save();
}

$content = <<<EOT
<div class="conect-us"><span class="title">Conect With Us</span> <a class="fb" href="#"><img src="{{skin url="images/fb.png"}}" alt="" /></a><a class="twitter" href="#"><img src="{{skin url="images/twitter.png"}}" alt="" /></a><a class="gplus" href="#"><img src="{{skin url="images/gplus.png"}}" alt="" /></a></div>
EOT;

$staticBlock = array(
    'title' => 'Conect Us',
    'identifier' => 'conect_us',
    'content' => $content,
    'is_active' => 1,
    'stores' => array(0)
);

$staticBlockModel = Mage::getModel('cms/block')->load('conect_us');

if($id = $staticBlockModel->getBlockId()){
    $staticBlockModel->setData($staticBlock)->setBlockId($id)->save();
}else{
    $staticBlockModel->setData($staticBlock)->save();
}

$content = <<<EOT
<div class="payment-partners"><span class="title">Payment Partners</span> <a href="#"><img src="{{skin url="images/visa.png"}}" alt="" /></a><a href="#"><img src="{{skin url="images/klikbca.png"}}" alt="" /></a><a href="#"><img src="{{skin url="images/klikpay.png"}}" alt="" /></a><a href="#"><img src="{{skin url="images/bca.png"}}" alt="" /></a><a href="#"><img src="{{skin url="images/bni.png"}}" alt="" /></a><a href="#"><img src="{{skin url="images/mandiri.png"}}" alt="" /></a></div>
EOT;

$staticBlock = array(
    'title' => 'Payment Partners',
    'identifier' => 'payment_partners',
    'content' => $content,
    'is_active' => 1,
    'stores' => array(0)
);

$staticBlockModel = Mage::getModel('cms/block')->load('payment_partners');

if($id = $staticBlockModel->getBlockId()){
    $staticBlockModel->setData($staticBlock)->setBlockId($id)->save();
}else{
    $staticBlockModel->setData($staticBlock)->save();
}

$content = <<<EOT
<div class="shipping-partners"><span class="title">Shipping Partners</span> <a href="#"><img src="{{skin url="images/jne.png"}}" alt="" /></a><a href="#"><img src="{{skin url="images/rpx.png"}}" alt="" /></a></div>
EOT;

$staticBlock = array(
    'title' => 'Shipping Partners',
    'identifier' => 'shipping_partners',
    'content' => $content,
    'is_active' => 1,
    'stores' => array(0)
);

$staticBlockModel = Mage::getModel('cms/block')->load('shipping_partners');

if($id = $staticBlockModel->getBlockId()){
    $staticBlockModel->setData($staticBlock)->setBlockId($id)->save();
}else{
    $staticBlockModel->setData($staticBlock)->save();
}

$content = <<<EOT
<p>Bilna.com merupakan online baby shop yang menjual berbagai perlengkapan bayi, ibu hamil, dan kebutuhan ibu menyusui dengan kualitas terbaik dan harga murah. Anda dapat menemukan ribuan produk yang lengkap, mulai dari diaper, susu formula, perlengkapan mandi, botol dot, mainan, pakaian bayi, tempat tidur bayi, kursi makan, alat gendong, baby bouncer, car seat, stroller hingga breast pump dan thermometer pun dapat anda temui di toko bayi Bilna. Produk yang kami jual berasal dari berbagai brand terkenal dan terpercaya seperti Pigeon, Nestle, SGM, Sebamed, Morinaga, Mamy Poko, Prenagen, Huggies, Dancow, Philips AVENT, dan masih banyak lagi.</p>
<p>Anda dapat menghemat waktu dan tenaga dengan berbelanja perlengkapan bayi anda di Bilna.com karena belanja terasa mudah dan menyenangkan. Anda dapat pula menghubungi costumer care kami apabila ada produk yang anda butuhkan namun belum ada di bilna.com. Customer care kami yang ramah akan siap sedia untuk membantu Anda yang memerlukan informasi karena kami mengutamakan kepuasan pelanggan dalam berbelanja. Kami juga menawarkan pengiriman produk dengan pelayanan kurir terbaik untuk memastikan produk Anda tiba dengan kondisi baik dan tepat waktu.</p>
EOT;

$staticBlock = array(
    'title' => 'About Us',
    'identifier' => 'about_us',
    'content' => $content,
    'is_active' => 1,
    'stores' => array(0)
);

$staticBlockModel = Mage::getModel('cms/block')->load('about_us');

if($id = $staticBlockModel->getBlockId()){
    $staticBlockModel->setData($staticBlock)->setBlockId($id)->save();
}else{
    $staticBlockModel->setData($staticBlock)->save();
}

$content = <<<EOT
Care Center {{config path="general/store_information/phone"}}
EOT;

$staticBlock = array(
    'title' => 'Store Phone',
    'identifier' => 'store_phone',
    'content' => $content,
    'is_active' => 1,
    'stores' => array(0)
);

$staticBlockModel = Mage::getModel('cms/block')->load('store_phone');

if($id = $staticBlockModel->getBlockId()){
    $staticBlockModel->setData($staticBlock)->setBlockId($id)->save();
}else{
    $staticBlockModel->setData($staticBlock)->save();
}

$content = <<<EOT
<div class="media-partners"><span class="title">As Featured On:</span>
<ul class="media-partners-wrapper">
<li><img src="{{skin url="images/urbanmama.png"}}" alt="" /></li>
<li><img src="{{skin url="images/ayahbunda.png"}}" alt="" /></li>
<li><img src="{{skin url="images/mommiesdaily.png"}}" alt="" /></li>
<li><img src="{{skin url="images/parent.png"}}" alt="" /></li>
</ul>
</div>
EOT;

$staticBlock = array(
    'title' => 'Media Partners',
    'identifier' => 'media_partners',
    'content' => $content,
    'is_active' => 1,
    'stores' => array(0)
);

$staticBlockModel = Mage::getModel('cms/block')->load('media_partners');

if($id = $staticBlockModel->getBlockId()){
    $staticBlockModel->setData($staticBlock)->setBlockId($id)->save();
}else{
    $staticBlockModel->setData($staticBlock)->save();
}

$content = <<<EOT
<div class="menu-sale-giftideas">
<a href="{{store direct_url="sale.html"}}" class="sale">Sale</a>
<a href="{{store direct_url="gift-ideas.html"}}" class="giftideas">Gift Ideas</a>
<div class="clear">&nbsp;</div>
</div>
EOT;

$staticBlock = array(
    'title' => 'Menu Sale & Gift Ideas',
    'identifier' => 'sale_giftideas',
    'content' => $content,
    'is_active' => 1,
    'stores' => array(0)
);

$staticBlockModel = Mage::getModel('cms/block')->load('sale_giftideas');

if($id = $staticBlockModel->getBlockId()){
    $staticBlockModel->setData($staticBlock)->setBlockId($id)->save();
}else{
    $staticBlockModel->setData($staticBlock)->save();
}

$content = <<<EOT
<ul class="block-wrapper">
<li>
<div class="block-bottom">
<p>Shopping Tips &amp; Tricks:</p>
<span>Diapers!</span><br /><br />
<p><a class="block-link" href="#">Read</a></p>
</div>
</li>
<li>
<div class="block-bottom"><span>Weekly Pregnancy Stage</span>
<p>Check Your Pregnancy!</p>
<br /><br />
<p><a class="block-link" href="#">Check Now!</a></p>
</div>
</li>
<li>
<div class="block-bottom">
<p>Punya pertanyaan<br /> seputar gizi &amp; kesehatan si kecil?</p>
<span>ask bilna&rsquo;s team!</span>
<p><a class="block-link" href="#">Ask Now!</a></p>
</div>
</li>
</ul>
<ul class="block-wrapper">
<li>
<div class="block-bottom">
<p>Nikmati</p>
<span>Cicilan %!</span><br /><br />
<p><a class="block-link" href="#">Syarat &amp; Ketentuan</a></p>
</div>
</li>
<li>
<div class="block-bottom"><span>Help</span><br /><br />
<p><a class="block-link link-how-to-buy" href="#">How to Buy?</a><a class="block-link link-shipping-table" href="#">Shipping Cost Table</a><a class="block-link link-other-help" href="#">Other Helps</a></p>
</div>
</li>
<li>
<div class="block-bottom"><span>More on Bilna</span><br /><br />
<p><a class="block-link link-bilna-credit" href="#">Bilna Credits</a><a class="block-link link-reseller-program" href="#">Reseller Program</a><a class="block-link link-gift-voucher" href="#">Gift Voucher</a></p>
</div>
</li>
</ul>
EOT;

$staticBlock = array(
    'title' => 'Block Home',
    'identifier' => 'block_home',
    'content' => $content,
    'is_active' => 1,
    'stores' => array(0)
);

$staticBlockModel = Mage::getModel('cms/block')->load('block_home');

if($id = $staticBlockModel->getBlockId()){
    $staticBlockModel->setData($staticBlock)->setBlockId($id)->save();
}else{
    $staticBlockModel->setData($staticBlock)->save();
}

$content = <<<EOT
<a href="#"><img src="{{skin url="images/banner-advertise-1.jpg"}}" alt="" /></a>
EOT;

$staticBlock = array(
    'title' => 'Banner Left',
    'identifier' => 'small_banner_left',
    'content' => $content,
    'is_active' => 1,
    'stores' => array(0)
);

$staticBlockModel = Mage::getModel('cms/block')->load('small_banner_left');

if($id = $staticBlockModel->getBlockId()){
    $staticBlockModel->setData($staticBlock)->setBlockId($id)->save();
}else{
    $staticBlockModel->setData($staticBlock)->save();
}

$content = <<<EOT
<a href="#"><img src="{{skin url="images/banner-advertise-2.jpg"}}" alt="" /></a>
EOT;

$staticBlock = array(
    'title' => 'Banner Right',
    'identifier' => 'small_banner_right',
    'content' => $content,
    'is_active' => 1,
    'stores' => array(0)
);

$staticBlockModel = Mage::getModel('cms/block')->load('small_banner_right');

if($id = $staticBlockModel->getBlockId()){
    $staticBlockModel->setData($staticBlock)->setBlockId($id)->save();
}else{
    $staticBlockModel->setData($staticBlock)->save();
}

$content = <<<EOT
<a href="#"><img src="{{skin url="images/banner-small.jpg"}}" alt="" /></a>
<a href="#"><img src="{{skin url="images/banner-gift-voucher.png"}}" alt="" /></a>
<a href="#"><img src="{{skin url="images/banner-small1.jpg"}}" alt="" /></a>
EOT;

$staticBlock = array(
    'title' => 'Banner Right Side',
    'identifier' => 'banner_right',
    'content' => $content,
    'is_active' => 1,
    'stores' => array(0)
);

$staticBlockModel = Mage::getModel('cms/block')->load('banner_right');

if($id = $staticBlockModel->getBlockId()){
    $staticBlockModel->setData($staticBlock)->setBlockId($id)->save();
}else{
    $staticBlockModel->setData($staticBlock)->save();
}

$installer->endSetup();
