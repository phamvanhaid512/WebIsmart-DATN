<style>
    #slider-wp .item img {
    height: 500px;
    width: 100% !important;
}
</style>
<div class="section" id="slider-wp">
    <div class="section-detail">
        @foreach ($sliders as $item)
            <div class="item"  width="878px" height="500px" >
                <img class="slider" width="878px" height="500px" src="{{ asset($item->image_path) }}" alt="{{ $item->image_name }}">
            </div>
        @endforeach
    </div>
</div>
