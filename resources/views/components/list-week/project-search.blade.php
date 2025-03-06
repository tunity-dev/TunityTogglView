<div class="relative w-64">
    <input type="text" id="logProject" placeholder="Zoek een project..." 
        class="w-full p-2 rounded-lg border border-gray-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-gray-900 dark:text-white"
        oninput="filterProjects()" autocomplete="off">
    <ul id="projectList" class="absolute w-full bg-white dark:bg-zinc-800 shadow-md rounded-lg mt-1 hidden max-h-40 overflow-y-auto"></ul>
</div>

<script>
    function filterProjects() {
        let input = document.getElementById("logProject").value.trim();
        let ul = document.getElementById("projectList");

        if (input.length < 2) {
            ul.innerHTML = "";
            ul.style.display = "none";
            return;
        }

        fetch(`/projects/search?q=${input}`)
            .then(response => response.json())
            .then(data => {
                ul.innerHTML = "";
                data.forEach(project => {
                    let li = document.createElement("li");
                    li.textContent = project.name;
                    li.classList.add("p-2", "cursor-pointer", "hover:bg-gray-200", "dark:hover:bg-zinc-700");
                    li.onclick = () => selectProject(project.name);
                    ul.appendChild(li);
                });
                ul.style.display = "block";
            })
            .catch(error => console.error('Fout bij ophalen projecten:', error));
    }

    function selectProject(name) {
        document.getElementById("logProject").value = name;
        document.getElementById("projectList").style.display = "none";
    }
</script>