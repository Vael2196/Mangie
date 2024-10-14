@props(['key', 'tag'])

<div {{ $attributes->merge([ 'class' => 'bg-gray-100 rounded-md text-nowrap flex items-center space-x-1 px-1'])}}>
    <i id="cookieTag{{$key}}" class="fa-solid fa-x fa-xs hover:cursor-pointer hover:bg-gray-200 py-3 px-2 rounded-md"></i>
    <h1 class="text-base">{{ $tag }}</h1>
</div>

<script>
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
    }

    function appendStorageItem(field, key, value){
        let data = localStorage.getItem(field);
        data = data ? JSON.parse(data) : {};
        data[key] = value;
        localStorage.setItem(field, JSON.stringify(data));
    }

    function getStorageItem(field){
        let data = localStorage.getItem(field);
        return data ? JSON.parse(data) : {};
    }

    function removeStorageItem(field, key){
        let data = localStorage.getItem(field);
        data = data ? JSON.parse(data) : {};
        delete data[key];
        localStorage.setItem(field, JSON.stringify(data));
    }

    window.addEventListener('DOMContentLoaded', e => {
        var key='<?php echo $key; ?>';
        let cookieTag = document.getElementById(`cookieTag${key}`);

        let value = getCookie(key);

        cookieTag.addEventListener('click', e => {
            document.cookie = `${key}=; expires=Thu, 01 Jan 1970 00:00:00 UTC;`;
            removeStorageItem('filter', key);

            console.log(getStorageItem('filter'));
            location.reload();
        });
    });
</script>
