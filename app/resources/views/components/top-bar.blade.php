@props(["title", "user"])

<div class="bg-gray-100 dark:bg-gray-800 border-b-2 px-4 py-2 mb-7 flex justify-between">
    <h1 class="text-3xl my-5 mx-2 dark:text-white font-bold">{{$title}}</h1>
    {{-- Create the icon such that when it is clicked, it will reveal a hidden extra information page about profile --}}
    <a class = "flex items-center hover:cursor-pointer"><i class="fa-solid fa-user fa-3x"></i></a>
</div>
<div class = "hidden" id="profile-detail">
    <x-profile-detail :user="$user"/>
</div>

<script>
    // when clicking anywhere else the popup disappears
    document.addEventListener('click', e => {
        const profileDetail = document.getElementById('profile-detail');
        if (profileDetail.classList.contains('hidden')) return;
        if (e.target.closest('.fa-user')) return;
        profileDetail.classList.add('hidden');
    });

    // show detailed profile view when clicking on the icon
    document.addEventListener('DOMContentLoaded', function () {
        const profileIcon = document.querySelector('.fa-user');
        const profileDetail = document.getElementById('profile-detail');

        profileIcon.addEventListener('click', e => {
            profileDetail.classList.toggle('hidden');
        }, false);
    });
</script>
