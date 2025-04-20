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

            items.forEach(item => {
                const rawItemTitle = item.dataset.title.toLowerCase();
                const itemTitle = normalizeString(rawItemTitle);
                
                const itemCategories = item.dataset.categories.split(' ');
                const matchesSearch = itemTitle.includes(searchTerm);
                const matchesCategories = activeCategories.length === 0 || 
                    activeCategories.some(cat => itemCategories.includes(cat));

                item.style.display = (matchesSearch && matchesCategories) ? 'block' : 'none';
            });
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
    });
});

