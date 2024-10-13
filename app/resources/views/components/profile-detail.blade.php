{{-- Profile Detail Component right underneath the icon --}}
<div class="absolute top-16 right-0 h-auto w-full p-5 flex justify-center items-center z-30 bg-black bg-opacity-0">
    <div class="flex">
        <div class="rounded-lg p-5 w-1/2 h-full bg-white dark:bg-gradient-to-l from-slate-700 to-gray-900 border-2 flex">
            <div class="flex flex-col w-1/2 h-full">
                <div class="mb-10">
                    <div class="flex w-full h-full bg-blue-200 dark:bg-blue-600 text-white dark:text-white items-center justify-center">
                        <h1>{{$user->name}}</h1>
                    </div>

                    {{-- Create a button that logs out --}}
                    <form method="POST" action="{{ route('logout')}}">
                        @csrf
                        <button class="abolsute bottom-0 bg-blue-600 hover:bg-blue-400 text-white text-sm py-1 px-2">
                            Logout
                        </button>
                    </form>

                    <form method="GET" action={{ route('profile.edit') }}>
                        @csrf
                        <button class="bg-blue-600 hover:bg-blue-400 text-white text-sm py-1 px-2">
                            Profile
                        </button>
                    </form>

                    <h3>Description</h3>
                    <p>This is a description of the profile and its purpose is to describe the profile and the reason for this is to fill up the word count and make a buffer layer so that the text can be sized correctly</p>
                </div>
                <div>
                    <h3 class="mb-4">Activity</h3>
                    <div class="flex justify-between w-full">
                        {{-- Example Buttons --}}
                    </div>
                    <div class="flex justify-between mb-4">
                        <p><span>XXXXXXXXX</span> changed the status</p>
                        <p>(09/09/2024)</p>
                    </div>
                    <div>
                        <h5>To DO:</h5>
                        <p>This is a description and its purpose is to describe the task and the reason for this is to fill up the word count and make a buffer layer so that the text can be sized correctly</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
