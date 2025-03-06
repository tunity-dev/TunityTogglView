<x-layouts.app>
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div class="flex justify-between items-center bg-gray-100 dark:bg-zinc-800 p-4 rounded-xl shadow">
            <input type="text" id="logDescription" placeholder="Wat ben je aan het doen?" 
                class="flex-1 p-2 rounded-lg border border-gray-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white">
            <!-- Play/Stop button --> 
            <button id="toggleTimer" class="ml-4 px-3 py-3 bg-orange-600 text-white hover:bg-orange-700 flex items-center rounded-full">
                <svg id="playIcon" class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-5.197-3.742A1 1 0 008 8v8a1 1 0 001.555.832l5.197-3.742a1 1 0 000-1.664z" />
                </svg>
                <svg id="stopIcon" class="w-5 h-5 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6h12v12H6z" />
                </svg>
            </button>
        </div>

        <!-- Week kalender -->
        <div class="grid grid-cols-8">
            
            <!-- Tijdkolom -->
            <div class="flex flex-col pr-2 w-16 mt-13">
                @for ($hour = 0; $hour <= 23; $hour++)
                    <div class="h-12 flex items-center dark:border-neutral-700 text-gray-900 dark:text-white text-sm pl-2">
                        {{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}:00
                    </div>
                @endfor
            </div>

            <!-- Dagen + tijdlogs -->
            <div class="grid grid-cols-7 col-span-7 border-l border-neutral-300 dark:border-neutral-700">
                @php 
                    $dates = ['3', '4', '5', '6', '7', '8', '9'];
                @endphp
                @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $index => $day)
                <div class="flex flex-col items-center border-r border-neutral-300 dark:border-neutral-700 
                            border-b pb-2">
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $dates[$index] }}</span>
                        <span class="text-xs uppercase tracking-wider text-gray-600 dark:text-gray-400">{{ $day }}</span>
                        <span id="loggedHours-{{ $day }}" class="text-xs text-gray-500 dark:text-gray-400 mt-1">0:00</span>
                    </div>
                @endforeach
            

                <!-- Tijdregistratie per dag -->
                @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $day)
                    <div id="logContainer-{{ $day }}" class="relative flex flex-col bg-white dark:bg-zinc-800 h-[calc(24*3rem)] overflow-hidden border-r border-neutral-300 dark:border-neutral-700">
                        @for ($hour = 0; $hour <= 23; $hour++)
                            <div class="h-12 border-b border-neutral-200 dark:border-neutral-700 relative"></div>
                        @endfor
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <script>
        let activeLog = null;
        let startTime = null;
        let selectedDay = 'Mon';

        document.getElementById('toggleTimer').addEventListener('click', function () {
            let playIcon = document.getElementById('playIcon');
            let stopIcon = document.getElementById('stopIcon');

            if (!activeLog) {
                startTime = new Date();
                activeLog = document.createElement('div');
                activeLog.classList.add('absolute', 'bg-orange-300', 'dark:bg-orange-700', 'rounded-lg', 'p-2', 'text-xs', 'text-black', 'dark:text-white', 'shadow-md');
                activeLog.innerText = document.getElementById('logDescription').value || "Nieuwe log";

                let hour = startTime.getHours();
                let minute = startTime.getMinutes();
                let topPosition = hour * 3 * 16 + (minute / 60) * (3 * 16); // Pixelberekening voor correcte plaatsing

                activeLog.style.top = `${topPosition}px`;
                activeLog.style.left = "5px"; 
                activeLog.style.width = "90%";

                document.getElementById(`logContainer-${selectedDay}`).appendChild(activeLog);

                playIcon.classList.add('hidden');
                stopIcon.classList.remove('hidden');
            } else {
                let endTime = new Date();
                let duration = Math.round((endTime - startTime) / 1000);
                let hours = Math.floor(duration / 3600);
                let minutes = Math.floor((duration % 3600) / 60);
                let seconds = duration % 60;

                let totalTime = `${hours}:${minutes}:${seconds}`;
                document.getElementById(`loggedHours-${selectedDay}`).innerText = totalTime;

                activeLog.innerText += ` (${totalTime})`;
                activeLog = null;
                playIcon.classList.remove('hidden');
                stopIcon.classList.add('hidden');
            }
        });
    </script>
</x-layouts.app>
