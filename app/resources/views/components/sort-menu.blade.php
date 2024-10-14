{{-- Sort Menu --}}
<div id="taskSortButton" class="hover:cursor-pointer hover:bg-gray-100 border rounded px-2 items-center py-1 dark:bg-gray-700 dark:text-white">
    <p>Sort</p>
</div>
<div class="hidden absolute z-30" id="taskSortMenu">
    <div class="flex items-start flex-wrap bg-gray-100 px-1 py-0.5">
        <div class="flex flex-col pr-2 border-r-2 border-gray-300 h-full" id="sortChildren">
            @foreach (['title', 'description', 'priority', 'labels', 'story points', 'time log'] as $label)
            <div class='bg-white border px-4 py-2 text-start leading-5 dark:bg-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-500 hover:cursor-pointer focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out flex space-x-3'
                id="sortBy{{$label}}">
                <h1>{{ $label }}</h1>
            </div>
            @endforeach
        </div>

        <div class="flex flex-col justify-between pl-2">
            <div id="sortDirection">
                <div class='bg-white border px-4 py-2 text-start leading-5 dark:bg-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-500 hover:cursor-pointer focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out flex space-x-3'
                    id="sortByasc">
                    <h1>Ascending</h1>
                </div>

                <div class='bg-white border px-4 py-2 text-start leading-5 dark:bg-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-500 hover:cursor-pointer focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out flex space-x-3'
                    id="sortBydesc">
                    <h1>Descending</h1>
                </div>
            </div>
            <div class='bg-slate-600 border px-4 py-2 text-start leading-5 dark:bg-gray-600 text-white dark:text-gray-300 hover:bg-slate-500 dark:hover:bg-gray-500 hover:cursor-pointer focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out flex space-x-3'
                id="sortBySubmit">
                <h1>Submit</h1>
            </div>
        </div>
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

    // Reset cookies on page load (Make sure they are correct)
    document.addEventListener('DOMContentLoaded', e => {
        let sort_obj = JSON.parse(localStorage.getItem('sort'));

        document.cookie = `sort=${sort_obj['sort']};`;
        document.cookie = `direction=${sort_obj['direction']};`;

        console.log(getCookie('sort'));
        console.log(getCookie('direction'));
    });

    window.addEventListener('DOMContentLoaded', e => {
        let form_dict = {};
        let taskSortButton = document.getElementById('taskSortButton');
        let taskSortMenu = document.getElementById('taskSortMenu');
        let sortMenuItems = document.getElementById('sortChildren').children;
        let sortMenuDirections = document.getElementById('sortDirection').children;
        let sortBySubmit = document.getElementById('sortBySubmit');

        // Reset menus on right click anywhere outside
        document.addEventListener('contextmenu', e => {
            taskSortMenu.classList.add('hidden');

            // Reset sort dictionary
            form_dict = {};
        });

        // Reset menus on right click anywhere outside
        document.addEventListener('click', e => {
            taskSortMenu.classList.add('hidden');

            // Reset sort dictionary
            form_dict = {};
        });

        taskSortButton.addEventListener('click', e => {
            e.stopPropagation();
            const rect = taskSortButton.getBoundingClientRect();
            taskSortMenu.style.left = (window.scrollX + rect.left - 20) + 'px';
            taskSortMenu.style.top = (window.scrollY + rect.top + rect.height) + 'px';
            taskSortMenu.classList.remove('hidden');
        });

        // Sort sub menus
        for(let item of sortMenuItems){
            item.addEventListener('click', e => {
                e.stopPropagation();
                let sort = item.id.replace('sortBy', '');
                console.log(sort);

                form_dict['sort'] = sort;
            });
        };

        for(let item of sortMenuDirections){
            item.addEventListener('click', e => {
                e.stopPropagation();
                let direction = item.id.replace('sortBy', '');
                console.log(direction);
                form_dict['direction'] = direction;
            })
        }

        sortBySubmit.addEventListener('click', e => {
            // Break if either a sorting option and direction are not chosen
            if(!(form_dict['sort'] && form_dict['direction'])){return;}

            localStorage.setItem('sort', JSON.stringify(form_dict));
            document.cookie = `sort=${form_dict['sort']};`;
            document.cookie = `direction=${form_dict['direction']};`;

            console.log(getCookie('sort'));
            console.log(getCookie('direction'));
            location.reload();
        })
    });
</script>
