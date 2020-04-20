
{if $productPrice < 1000 }
<script src="https://widgets.shophumm.com.au/content/scripts/price-info.js?productPrice={$productPrice}&LittleThings"></script>
{else}
    <script src="https://widgets.shophumm.com.au/content/scripts/price-info.js?productPrice={$productPrice}&BigOnly"></script>
{/if}