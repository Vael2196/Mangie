@props(['key', 'direction'])

<div {{ $attributes->merge([ 'class' => 'bg-gray-100 rounded-md text-nowrap flex items-center space-x-2 pl-1 pr-2 dark:bg-gray-700'])}}>
    <i id="sortTag" class="fa-solid fa-x fa-xs hover:cursor-pointer hover:bg-gray-500 py-3 px-2 rounded-md dark:bg-gray-700"></i>
    <h1 class="text-base">{{ $key }}</h1>
    <div>
        @if($direction === "asc")
            <i class="fa-solid fa-arrow-up fa-sm"></i>
        @else
            <i class="fa-solid fa-arrow-down fa-sm"></i>
        @endif
    </div>
</div>

<script>
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
    }

    function getStorageItem(field){
        let data = localStorage.getItem(field);
        return data ? JSON.parse(data) : {};
    }

    function resetStorageItem(field){
        localStorage.setItem(field, "");
    }

    window.addEventListener('DOMContentLoaded', e => {
        var key='<?php echo $key; ?>';
        var direction='<?php echo $direction; ?>';
        let cookieTag = document.getElementById(`sortTag`);

        cookieTag.addEventListener('click', e => {
            document.cookie = `sort=; expires=Thu, 01 Jan 1970 00:00:00 UTC;`;
            document.cookie = `direction=; expires=Thu, 01 Jan 1970 00:00:00 UTC;`;
            console.log(getStorageItem('sort'));
            resetStorageItem('sort');
            location.reload();
        });
    });
</script>
