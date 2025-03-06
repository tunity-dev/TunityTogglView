<div class="flex justify-between items-center bg-gray-100 dark:bg-zinc-800 p-4 rounded-xl shadow gap-5">
    <x-list-week.project-search />

    <input type="text" id="logDescription" placeholder="What are you working on?" 
        class="flex-1 p-2 rounded-lg border border-gray-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white">

    <button id="toggleTimer" class="px-3 py-3 bg-orange-600 text-white hover:bg-orange-700 flex items-center rounded-full">
        <svg id="playIcon" class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-5.197-3.742A1 1 0 008 8v8a1 1 0 001.555.832l5.197-3.742a1 1 0 000-1.664z" />
        </svg>
        <svg id="stopIcon" class="w-5 h-5 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6h12v12H6z" />
        </svg>
    </button>
</div>

<script>
    let activeLog = null;
    let startTime = null;
    let selectedDay = 'Mon';

    document.getElementById('toggleTimer').addEventListener('click', function () {
        let playIcon = document.getElementById('playIcon');
        let stopIcon = document.getElementById('stopIcon');

        let description = document.getElementById('logDescription').value.trim();
        let project = document.getElementById('logProject').value.trim();

        if (!activeLog) {
            startTime = new Date();
            activeLog = document.createElement('div');
            activeLog.classList.add('absolute', 'bg-orange-300', 'dark:bg-orange-700', 'rounded-lg', 'p-2', 'text-xs', 'text-black', 'dark:text-white', 'shadow-md');
            activeLog.innerHTML = `<strong>${project}</strong><br>${description}`;

            let hour = startTime.getHours();
            let minute = startTime.getMinutes();
            let topPosition = hour * 3 * 16 + (minute / 60) * (3 * 16);

            activeLog.style.top = `${topPosition}px`;
            activeLog.style.left = "5px"; 
            activeLog.style.width = "90%";

            document.getElementById(`logContainer-${selectedDay}`).appendChild(activeLog);

            playIcon.classList.add('hidden');
            stopIcon.classList.remove('hidden');
        } else {
            if (!description || !project) {
                alert("Insert a description and project name");
                return;
            }

            activeLog = null;
            playIcon.classList.remove('hidden');
            stopIcon.classList.add('hidden');
        }
    });
</script>