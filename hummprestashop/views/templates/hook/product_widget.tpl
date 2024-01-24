
{if $productPrice < 1000 }
<script src="https://bpi.humm-au.com/au/content/scripts/price-info_sync.js?productPrice={$productPrice}"></script>
{else}
    <script src="https://bpi.humm-au.com/au/content/scripts/price-info_sync.js?productPrice={$productPrice}&BigOnly"></script>
{/if}