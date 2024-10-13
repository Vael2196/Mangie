{{-- Filter Menu --}}
<div id="taskFilterButton" class="hover:cursor-pointer hover:bg-gray-100 border rounded px-2 items-center py-1 dark:bg-gray-700 dark:text-white">
    <p>Add Filter</p>
</div>
<div class="hidden absolute z-30" id="taskFilterMenu">
    <div class="2xl:flex 2xl:items-start">
        <div class="flex flex-col">
            {{-- priority filtering Button --}}
            <div
                class='bg-white border px-4 py-2 items-center leading-5 dark:bg-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-500 hover:cursor-pointer focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out rounded flex space-x-3'>
                <div class = "text-center"
                    onmouseover="document.getElementById('filterPriorityMenu').classList.remove('hidden');
                                document.getElementById('filterLabelsMenu').classList.add('hidden');">priority
                </div>
                <div class="text-center"><i class="fa-solid fa-angle-right"></i></div>
            </div>

            {{-- labels filtering Button --}}
            <div
                class='bg-white border px-4 py-2 items-center leading-5 dark:bg-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-500 hover:cursor-pointer focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out rounded flex space-x-3'>
                <div class = "text-center"
                    onmouseover="document.getElementById('filterLabelsMenu').classList.remove('hidden');
                                document.getElementById('filterPriorityMenu').classList.add('hidden');">labels
                </div>
                <div class="text-center"><i class="fa-solid fa-angle-right"></i></div>
            </div>
        </div>

        {{-- Priority filtering sub Menu--}}
        <div class="hidden shadow-lg" id="filterPriorityMenu">
            @foreach (["Low", "Medium", "High"] as $priority)
                <div class='bg-white border px-4 py-2 text-start leading-5 dark:bg-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-500 hover:cursor-pointer focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out flex space-x-3'
                    id="filterPriority{{$priority}}">
                    <h1>{{ $priority }}</h1>
                </div>
            @endforeach
        </div>

        {{-- Labels filtering sub Menu --}}
        <div class="hidden shadow-lg" id="filterLabelsMenu">
            @foreach (["API", "Backend", "Frontend", "UI/UX", "Database"] as $label)
                <div class='bg-white border px-4 py-2 text-start leading-5 dark:bg-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-500 hover:cursor-pointer focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out flex space-x-3'
                    id="filterLabel{{$label}}">
                    <h1>{{ $label }}</h1>
                </div>
            @endforeach
        </div>
    </div>
</div>


{{-- Script --}}
<script>
    window.addEventListener('DOMContentLoaded', e => {
        // Filter Menu
        const taskFilterButton = document.getElementById('taskFilterButton');
        const taskFilterMenu = document.getElementById('taskFilterMenu');
        const filterPriorityMenu = document.getElementById('filterPriorityMenu');
        const filterLabelsMenu = document.getElementById('filterLabelsMenu');

        const filterPriorityMenuItems = filterPriorityMenu.children;
        const filterLabelsMenuItems = filterLabelsMenu.children

        // Reset menus on right click anywhere outside
        document.addEventListener('contextmenu', e => {
            filterPriorityMenu.classList.add('hidden');
            filterLabelsMenu.classList.add('hidden');
            taskFilterMenu.classList.add('hidden');
        });

        document.addEventListener('click', e => {
            filterPriorityMenu.classList.add('hidden');
            filterLabelsMenu.classList.add('hidden');
            taskFilterMenu.classList.add('hidden');
        });

        // Show filter menu when clicking filter button
        taskFilterButton.addEventListener('click', e => {
            e.stopPropagation();
            const rect = taskFilterButton.getBoundingClientRect();
            taskFilterMenu.style.left = (window.scrollX + rect.left - 20) + 'px';
            taskFilterMenu.style.top = (window.scrollY + rect.top + rect.height) + 'px';
            taskFilterMenu.classList.remove('hidden');
        });

        // Priority sub menus
        for(let item of filterPriorityMenuItems){
            item.addEventListener('click', e => {
                console.log(item.id);
                localStorage.setItem('filterType', 'priority');
                localStorage.setItem('filter', item.id.replace('filterPriority', ''));
                location.reload();
            });
        };

        // Labels sub menus
        for(let item of filterLabelsMenuItems){
            item.addEventListener('click', e => {
                console.log(item.id);
                localStorage.setItem('filterType', 'label');
                localStorage.setItem('filter', item.id.replace('filterLabel', ''));
                location.reload();
            });
        };
    });
</script>
