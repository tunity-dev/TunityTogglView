<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
    @foreach ($topUsers->take(3) as $index => $user)
        <x-list-users.user-card :user="$user" :index="$index" />
    @endforeach
</div>
