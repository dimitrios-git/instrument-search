// assets/js/frontend.js
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.instrument-search-block').forEach(block => {
        const searchInput = block.querySelector('.search-input');
        const items = block.querySelectorAll('.instrument-list li');
        
        const normalizeString = (str) => {
            return str.toLowerCase().replace(/[^a-z0-9]/g, '');
        };

        const filterInstruments = () => {
            const rawSearchTerm = searchInput.value.toLowerCase();
            const searchTerm = normalizeString(rawSearchTerm);
        
            let activeCategories = [];
        
            if(block.classList.contains('filter-style-dropdown')) {
                const select = block.querySelector('select.category-filter');
                activeCategories = select.value === 'all' ? [] : [select.value];
            }
            else if(block.classList.contains('filter-style-chips')) {
                const activeChip = block.querySelector('.category-chip.active');
                activeCategories = activeChip?.dataset.category === 'all' ?
                    [] : [activeChip?.dataset.category];
            }
            else if(block.classList.contains('filter-style-checkboxes')) {
                activeCategories = Array.from(
                    block.querySelectorAll('.category-checkbox input:checked')
                ).map(cb => cb.value);
            }
        
            let firstVisible = null;
        
            items.forEach(item => {
                item.classList.remove('highlighted-instrument'); // Remove previous highlight
        
                const rawItemTitle = item.dataset.title.toLowerCase();
                const itemTitle = normalizeString(rawItemTitle);
        
                const itemCategories = item.dataset.categories.split(' ');
                const matchesSearch = itemTitle.includes(searchTerm);
                const matchesCategories = activeCategories.length === 0 ||
                    activeCategories.some(cat => itemCategories.includes(cat));
        
                const shouldShow = matchesSearch && matchesCategories;
                item.style.display = shouldShow ? 'block' : 'none';
        
                if (shouldShow && !firstVisible) {
                    firstVisible = item;
                }
            });
        
            if (firstVisible) {
                firstVisible.classList.add('highlighted-instrument');
            }
        };

        // Event listeners
        const selectFilter = block.querySelector('select.category-filter');
        if(selectFilter) selectFilter.addEventListener('change', filterInstruments);

        block.querySelectorAll('.category-chip').forEach(chip => {
            chip.addEventListener('click', function() {
                block.querySelectorAll('.category-chip').forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                filterInstruments();
            });
        });

        block.querySelectorAll('.category-checkbox input').forEach(checkbox => {
            checkbox.addEventListener('change', filterInstruments);
        });

        const checkboxSearch = block.querySelector('.checkbox-search');
        if(checkboxSearch) {
            checkboxSearch.addEventListener('input', function() {
                const search = this.value.toLowerCase();
                block.querySelectorAll('.category-checkbox').forEach(cb => {
                    cb.style.display = cb.textContent.toLowerCase().includes(search) ? 'flex' : 'none';
                });
            });
        }

        searchInput.addEventListener('input', filterInstruments);

        searchInput.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault(); // Prevent form submission if inside a form
        
                // Find the first visible (highlighted) item dynamically
                const visibleItems = Array.from(block.querySelectorAll('.instrument-list li'))
                    .filter(item => item.style.display !== 'none');
        
                if (visibleItems.length > 0) {
                    const firstLink = visibleItems[0].querySelector('a');
                    if (firstLink) {
                        window.location.href = firstLink.href;
                    }
                }
            }
        });

    });
});

