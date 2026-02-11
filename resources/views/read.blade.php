<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0a0a0a">
    <title>Read &mdash; Midnight Pilgrim</title>
    <link rel="manifest" href="/manifest.json">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #0a0a0a;
            color: #c4c4c4;
            line-height: 1.6;
            min-height: 100vh;
        }

        nav {
            padding: 1.5rem;
            border-bottom: 1px solid #1a1a1a;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        nav a {
            color: #666;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.2s;
        }

        nav a:hover {
            color: #999;
        }

        nav .current {
            color: #c4c4c4;
        }
        
        /* Read-only mode toggle */
        .readonly-toggle {
            position: fixed;
            top: 1rem;
            right: 1rem;
            background: transparent;
            border: 1px solid #222;
            color: #444;
            padding: 0.4rem 0.8rem;
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.2s;
            border-radius: 2px;
            z-index: 1000;
        }
        
        .readonly-toggle:hover {
            border-color: #333;
            color: #666;
        }
        
        body.readonly-mode .item-actions {
            display: none;
        }

        .container {
            max-width: 680px;
            margin: 0 auto;
            padding: 3rem 1.5rem;
        }

        /* Quiet browsing - no feeds or dashboards */
        .filter {
            margin-bottom: 2rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            padding-bottom: 1rem;
            border-bottom: 1px solid #1a1a1a;
        }

        .filter button {
            background: transparent;
            border: 1px solid #333;
            color: #666;
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s;
            border-radius: 2px;
        }

        .filter button:hover,
        .filter button.active {
            border-color: #666;
            color: #c4c4c4;
        }

        /* Content cards - minimal */
        .item {
            padding: 1.5rem 0;
            border-bottom: 1px solid #1a1a1a;
        }

        .item:last-child {
            border-bottom: none;
        }

        .item-meta {
            font-size: 0.85rem;
            color: #555;
            margin-bottom: 0.5rem;
        }

        .item-body {
            color: #999;
            line-height: 1.7;
        }

        .item-body p {
            margin-bottom: 0.8rem;
        }

        .item a {
            color: #888;
            text-decoration: none;
            transition: color 0.2s;
        }

        .item a:hover {
            color: #c4c4c4;
        }

        /* Empty state */
        .empty {
            text-align: center;
            color: #444;
            padding: 4rem 1rem;
            font-size: 0.95rem;
        }

        /* Mobile */
        @media (max-width: 640px) {
            .container {
                padding: 2rem 1rem;
            }

            .filter {
                flex-direction: column;
            }

            .filter button {
                width: 100%;
            }
        }

        ::selection {
            background: #222;
            color: #eee;
        }
    </style>
</head>
<body>
    <button class="readonly-toggle" onclick="toggleReadonly()">Read-only</button>
    
    <nav>
        <div>
            <a href="/" style="color: #999; font-weight: 500;">Midnight Pilgrim</a>
            <span style="margin-left: 1.5rem; font-size: 0.85rem; color: #444;">{{ now()->format('F j') }}</span>
        </div>
        <div style="display: flex; gap: 1.5rem;">
            <a href="/write">Write</a>
            <a href="/read" class="current">Read</a>
            <a href="/conversation">Conversation</a>
            <a href="/adjacent-view">Adjacent</a>
            <a href="/sit">Sit</a>
            <a href="/export/vault" style="color: #666; font-size: 0.85rem; align-self: center;">Export All</a>
        </div>
    </nav>

    <div class="container">
        @php
            $allCount = count($items ?? []);
            $notesCount = collect($items ?? [])->where('type', 'note')->count();
            $quotesCount = collect($items ?? [])->where('type', 'quote')->count();
            $thoughtsCount = collect($items ?? [])->where('type', 'thought')->count();
        @endphp
        
        <!-- Search bar -->
        <div style="margin-bottom: 2rem;">
            <input 
                type="text" 
                id="search" 
                placeholder="Search your writings..." 
                style="width: 100%; background: transparent; border: 1px solid #333; color: #c4c4c4; padding: 0.75rem 1rem; font-size: 0.95rem; border-radius: 2px; outline: none;"
                autocomplete="off"
            >
        </div>
        
        <div class="filter">
            <button class="active" data-type="all">All <span style="opacity: 0.4;">{{ $allCount }}</span></button>
            <button data-type="note">Notes <span style="opacity: 0.4;">{{ $notesCount }}</span></button>
            <button data-type="quote">Quotes <span style="opacity: 0.4;">{{ $quotesCount }}</span></button>
            <button data-type="thought">Thoughts <span style="opacity: 0.4;">{{ $thoughtsCount }}</span></button>
            <button id="random-btn" style="margin-left: auto; border-color: #666; color: #8b8baf;">Random</button>
            <button id="expand-btn" style="border-color: #333; color: #666; margin-left: 0.5rem;">Expand</button>
            <span style="font-size: 0.8rem; color: #333; margin-left: 1rem;">j/k to browse</span>
        </div>

        <div id="content">
            @forelse($items ?? [] as $item)
                <div class="item" data-type="{{ $item['type'] ?? 'note' }}">
                    <div class="item-meta">
                        {{ $item['date'] ?? 'Recent' }} 
                        &middot; 
                        {{ ucfirst($item['type'] ?? 'note') }}
                    </div>
                    <div class="item-body">
                        @php
                            // Map type to route type parameter
                            $routeType = $item['type'] === 'note' ? 'notes' : ($item['type'] === 'quote' ? 'quotes' : 'thoughts');
                        @endphp
                        <a href="/view/{{ $routeType }}/{{ $item['slug'] ?? '' }}">
                            @if(!empty($item['title']))
                                <div style="font-weight: 400; color: #c4c4c4; margin-bottom: 0.5rem;">{{ $item['title'] }}</div>
                            @endif
                            <div class="item-excerpt" style="color: #999;">{{ Str::limit($item['body'] ?? '', 150) }}</div>
                            <div class="item-full" style="color: #999; display: none;">{!! Str::markdown($item['body'] ?? '') !!}</div>
                            @if(!empty($item['tags']) && is_array($item['tags']))
                                <div class="tags" style="margin-top: 0.75rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                    @foreach($item['tags'] as $tag)
                                        <span class="tag" data-tag="{{ $tag }}" style="font-size: 0.8rem; color: #666; border: 1px solid #333; padding: 0.2rem 0.6rem; border-radius: 2px; cursor: pointer;">{{ $tag }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </a>
                    </div>
                </div>
            @empty
                <div class="empty">
                    Nothing here yet.<br>
                    Your notes, quotes, and thoughts will appear here.
                </div>
            @endforelse
        </div>
    </div>

    <script>
        // PWA: Register service worker
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js');
        }

        // Simple filter (client-side for now)
        const filterButtons = document.querySelectorAll('.filter button:not(#random-btn)');
        filterButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                filterButtons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                const type = btn.dataset.type;
                applyFilters();
            });
        });

        // Search functionality
        const searchInput = document.getElementById('search');
        let searchTimeout;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                applyFilters();
            }, 300);
        });

        // Apply both type filter and search
        function applyFilters() {
            const searchTerm = searchInput.value.toLowerCase();
            const activeBtn = document.querySelector('.filter button.active');
            const type = activeBtn ? activeBtn.dataset.type : 'all';
            const allItems = document.querySelectorAll('.item');

            allItems.forEach(item => {
                const itemType = item.dataset.type;
                const itemText = item.textContent.toLowerCase();
                
                const matchesType = type === 'all' || itemType === type;
                const matchesSearch = !searchTerm || itemText.includes(searchTerm);
                
                item.style.display = (matchesType && matchesSearch) ? 'block' : 'none';
            });
        }

        // Tag filtering
        let selectedTag = null;
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('tag')) {
                e.preventDefault();
                e.stopPropagation();
                
                const tag = e.target.dataset.tag;
                
                // Toggle tag selection
                if (selectedTag === tag) {
                    selectedTag = null;
                    document.querySelectorAll('.tag').forEach(t => t.style.borderColor = '#333');
                } else {
                    selectedTag = tag;
                    document.querySelectorAll('.tag').forEach(t => {
                        t.style.borderColor = t.dataset.tag === tag ? '#8b8baf' : '#333';
                    });
                }
                
                // Filter items by tag
                const allItems = document.querySelectorAll('.item');
                allItems.forEach(item => {
                    if (!selectedTag) {
                        // Reset to normal filter
                        applyFilters();
                    } else {
                        const itemTags = Array.from(item.querySelectorAll('.tag')).map(t => t.dataset.tag);
                        const matchesTag = itemTags.includes(selectedTag);
                        item.style.display = matchesTag ? 'block' : 'none';
                    }
                });
            }
        });

        // Random note
        document.getElementById('random-btn').addEventListener('click', () => {
            const visibleItems = Array.from(document.querySelectorAll('.item')).filter(item => item.style.display !== 'none');
            if (visibleItems.length > 0) {
                const randomItem = visibleItems[Math.floor(Math.random() * visibleItems.length)];
                const link = randomItem.querySelector('a');
                if (link) link.click();
            }
        });

        // Keyboard navigation (j/k for vim-style browsing)
        let currentIndex = -1;
        const items = Array.from(document.querySelectorAll('.item'));
        
        document.addEventListener('keydown', (e) => {
            // j = next item
            if (e.key === 'j' && currentIndex < items.length - 1) {
                e.preventDefault();
                currentIndex++;
                items[currentIndex].scrollIntoView({ behavior: 'smooth', block: 'center' });
                items[currentIndex].style.background = '#0f0f0f';
                if (currentIndex > 0) items[currentIndex - 1].style.background = 'transparent';
            }
            
            // k = previous item
            if (e.key === 'k' && currentIndex > 0) {
                e.preventDefault();
                currentIndex--;
                items[currentIndex].scrollIntoView({ behavior: 'smooth', block: 'center' });
                items[currentIndex].style.background = '#0f0f0f';
                if (currentIndex < items.length - 1) items[currentIndex + 1].style.background = 'transparent';
            }
            
            // Enter = open current item
            if (e.key === 'Enter' && currentIndex >= 0) {
                e.preventDefault();
                const link = items[currentIndex].querySelector('a');
                if (link) link.click();
            }
            
            // Number keys 1-4 for filters
            if (['1', '2', '3', '4'].includes(e.key)) {
                e.preventDefault();
                const buttons = document.querySelectorAll('.filter button');
                const index = parseInt(e.key) - 1;
                if (buttons[index]) buttons[index].click();
            }
        });
        
        // Expand/collapse toggle
        let isExpanded = false;
        document.getElementById('expand-btn').addEventListener('click', function() {
            isExpanded = !isExpanded;
            const excerpts = document.querySelectorAll('.item-excerpt');
            const fulls = document.querySelectorAll('.item-full');
            
            if (isExpanded) {
                excerpts.forEach(el => el.style.display = 'none');
                fulls.forEach(el => el.style.display = 'block');
                this.textContent = 'Collapse';
                this.style.borderColor = '#8b8baf';
                this.style.color = '#8b8baf';
            } else {
                excerpts.forEach(el => el.style.display = 'block');
                fulls.forEach(el => el.style.display = 'none');
                this.textContent = 'Expand';
                this.style.borderColor = '#333';
                this.style.color = '#666';
            }
        });
        
        // Read-only mode toggle
        function toggleReadonly() {
            document.body.classList.toggle('readonly-mode');
            const isReadonly = document.body.classList.contains('readonly-mode');
            localStorage.setItem('readonlyMode', isReadonly ? 'true' : 'false');
            
            // Update button text
            document.querySelector('.readonly-toggle').textContent = isReadonly ? 'Edit Mode' : 'Read-only';
        }
        
        // Restore read-only mode from localStorage
        if (localStorage.getItem('readonlyMode') === 'true') {
            document.body.classList.add('readonly-mode');
            document.querySelector('.readonly-toggle').textContent = 'Edit Mode';
        }
    </script>
</body>
</html>
