{{-- Profile Detail Component right underneath the icon --}}
<div class="absolute top-16 right-0 h-auto w-full p-5 flex justify-center items-center z-30 bg-black bg-opacity-0">
    <div class="flex">
        <div class="rounded-lg p-5 w-full h-full bg-white dark:bg-gradient-to-l from-slate-700 to-gray-900 border-2 flex">
            <div class="flex flex-col w-1/2 h-full">
                <div class="mb-10">
                    <div class="flex w-full h-full bg-transparent dark:bg-transparent text-white dark:text-white justify-between">
                        <h1>Username: {{$user->name}} </h1>
                        @if (Auth::user()->admin == 1)
                            <h1 class="dark:text:white">Admin</h1>
                        @endif
                    </div>

                    <form method="GET" action={{ route('profile.edit') }}>
                        @csrf
                        <button class="bg-blue-600 hover:bg-blue-400 text-white text-sm py-1 px-2">
                            Edit Profile
                        </button>
                    </form>

                    {{-- Create a button that logs out --}}
                    <form method="POST" action="{{ route('logout')}}">
                        @csrf
                        <button class="abolsute bottom-0 bg-blue-600 hover:bg-blue-400 text-white text-sm py-1 px-2">
                            Logout
                        </button>
                    </form>

                    {{-- Only admins can see this button --}}

                    @if (Auth::user()->admin == 1)
                        <form method="GET" action="{{ route('profile.add-user')}}"
                            @csrf
                            <button class="bg-blue-600 hover:bg-blue-400 text-white text-sm py-1 px-2">
                                Add User
                            </button>
                        </form>
                    @endif

                    <p>"asjdkasjhfkjdhfkjasdhfkjdsahfjkahdsjkfhdsakfhdkslahfakdsvfahsdfkdjshfkjdshfkjsdhfkjdshfkhdkshfkjashfkjhsdf"</p>
                </div>
            </div>
        </div>
    </div>
</div>
