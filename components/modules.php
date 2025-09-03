<?php
session_start();
include_once '../backend/config.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/navbar.css">
    <link rel="stylesheet" href="../styles/footer.css">
    <link rel="stylesheet" href="../styles/modules.css">
    <link rel="stylesheet" href="../styles/general.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <title>Learning Modules - NutritionTrack</title>
</head>

<body>
    <?php include_once './navbar.php'; ?>
    
    <main>
        <div class="hero-section">
            <div class="hero-content">
                <div class="breadcrumb">
                    <a href="./home.php">Home</a>
                    <span><i class="fa-solid fa-chevron-right"></i></span>
                    <span>Learning Modules</span>
                </div>
                <h1>
                    <i class="fa-solid fa-graduation-cap"></i>
                    Learning Modules
                </h1>
                <p>Enhance your nutrition knowledge with our comprehensive learning modules and educational resources</p>
            </div>
        </div>

        <div class="filter-section">
            <div class="filter-container">
                <div class="search-box">
                    <input type="text" placeholder="Search modules..." id="searchInput">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <div class="filter-options">
                    <select id="categoryFilter">
                        <option value="all">All Categories</option>
                        <option value="basics">Nutrition Basics</option>
                        <option value="diet">Diet Planning</option>
                        <option value="health">Health Conditions</option>
                        <option value="cooking">Cooking & Recipes</option>
                    </select>
                    <select id="sortFilter">
                        <option value="newest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                        <option value="popular">Most Popular</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="modules-section">
            <div class="modules-container">
                <div class="modules-grid" id="modulesGrid">
                    <article class="module-card" data-category="basics" data-date="2024-03-15" data-title="Understanding Macronutrients" data-content="Learn about carbohydrates, proteins, and fats and their roles in maintaining optimal health and energy levels" data-popularity="95">
                        <div class="card-header">
                            <div class="category-badge modules">
                                <i class="fa-solid fa-book-open"></i>
                                Modules
                            </div>
                        </div>
                        <div class="card-image">
                            <img src="../assets/modules/module-1.jpg" alt="Macronutrients">
                        </div>
                        <div class="card-content">
                            <h2>Understanding Macronutrients</h2>
                            <p>Learn about carbohydrates, proteins, and fats and their roles in maintaining optimal health and energy levels. This comprehensive guide covers daily requirements and food sources.</p>
                        </div>
                        <div class="card-actions">
                            <a href="./module-detail.php?id=1" class="start-module-btn">
                                Start Learning
                                <i class="fa-solid fa-play"></i>
                            </a>
                        </div>
                    </article>

                    <article class="module-card" data-category="diet" data-date="2024-03-12" data-title="Meal Planning Fundamentals" data-content="Master the art of planning balanced, nutritious meals for the week while considering budget and dietary preferences" data-popularity="88">
                        <div class="card-header">
                            <div class="category-badge modules">
                                <i class="fa-solid fa-book-open"></i>
                                Modules
                            </div>
                        </div>
                        <div class="card-image">
                            <img src="../assets/modules/module-2.jpg" alt="Meal Planning">
                        </div>
                        <div class="card-content">
                            <h2>Meal Planning Fundamentals</h2>
                            <p>Master the art of planning balanced, nutritious meals for the week while considering budget and dietary preferences. Includes practical templates and shopping lists.</p>
                        </div>
                        <div class="card-actions">
                            <a href="./module-detail.php?id=2" class="start-module-btn">
                                Start Learning
                                <i class="fa-solid fa-play"></i>
                            </a>
                        </div>
                    </article>

                    <article class="module-card" data-category="health" data-date="2024-03-10" data-title="Nutrition for Diabetes Management" data-content="Comprehensive guide to managing diabetes through proper nutrition, blood sugar control, and lifestyle modifications" data-popularity="76">
                        <div class="card-header">
                            <div class="category-badge modules">
                                <i class="fa-solid fa-book-open"></i>
                                Modules
                            </div>
                        </div>
                        <div class="card-image">
                            <img src="../assets/modules/module-3.jpg" alt="Diabetes Nutrition">
                        </div>
                        <div class="card-content">
                            <h2>Nutrition for Diabetes Management</h2>
                            <p>Comprehensive guide to managing diabetes through proper nutrition, blood sugar control, and lifestyle modifications. Evidence-based strategies for better health outcomes.</p>
                        </div>
                        <div class="card-actions">
                            <a href="./module-detail.php?id=3" class="start-module-btn">
                                Start Learning
                                <i class="fa-solid fa-play"></i>
                            </a>
                        </div>
                    </article>

                    <article class="module-card" data-category="cooking" data-date="2024-03-08" data-title="Healthy Cooking Techniques" data-content="Learn essential cooking methods that preserve nutrients while creating delicious, healthy meals for you and your family" data-popularity="91">
                        <div class="card-header">
                            <div class="category-badge modules">
                                <i class="fa-solid fa-book-open"></i>
                                Modules
                            </div>
                        </div>
                        <div class="card-image">
                            <img src="../assets/modules/module-4.jpg" alt="Cooking Techniques">
                        </div>
                        <div class="card-content">
                            <h2>Healthy Cooking Techniques</h2>
                            <p>Learn essential cooking methods that preserve nutrients while creating delicious, healthy meals for you and your family. Includes video demonstrations and recipes.</p>
                        </div>
                        <div class="card-actions">
                            <a href="./module-detail.php?id=4" class="start-module-btn">
                                Start Learning
                                <i class="fa-solid fa-play"></i>
                            </a>
                        </div>
                    </article>

                    <article class="module-card" data-category="basics" data-date="2024-03-05" data-title="Micronutrients and Vitamins" data-content="Deep dive into essential vitamins and minerals, their functions, deficiency symptoms, and best food sources" data-popularity="83">
                        <div class="card-header">
                            <div class="category-badge modules">
                                <i class="fa-solid fa-book-open"></i>
                                Modules
                            </div>
                        </div>
                        <div class="card-image">
                            <img src="../assets/modules/module-5.jpg" alt="Micronutrients">
                        </div>
                        <div class="card-content">
                            <h2>Micronutrients and Vitamins</h2>
                            <p>Deep dive into essential vitamins and minerals, their functions, deficiency symptoms, and best food sources. Understand how to meet your daily requirements naturally.</p>
                        </div>
                        <div class="card-actions">
                            <a href="./module-detail.php?id=5" class="start-module-btn">
                                Start Learning
                                <i class="fa-solid fa-play"></i>
                            </a>
                        </div>
                    </article>

                    <article class="module-card" data-category="diet" data-date="2024-03-01" data-title="Reading Nutrition Labels" data-content="Master the skill of reading and interpreting nutrition labels to make informed food choices for better health" data-popularity="94">
                        <div class="card-header">
                            <div class="category-badge modules">
                                <i class="fa-solid fa-book-open"></i>
                                Modules
                            </div>
                        </div>
                        <div class="card-image">
                            <img src="../assets/modules/module-6.jpg" alt="Nutrition Labels">
                        </div>
                        <div class="card-content">
                            <h2>Reading Nutrition Labels</h2>
                            <p>Master the skill of reading and interpreting nutrition labels to make informed food choices for better health. Includes interactive examples and practice exercises.</p>
                        </div>
                        <div class="card-actions">
                            <a href="./module-detail.php?id=6" class="start-module-btn">
                                Start Learning
                                <i class="fa-solid fa-play"></i>
                            </a>
                        </div>
                    </article>

                    <article class="module-card" data-category="health" data-date="2024-02-28" data-title="Heart-Healthy Nutrition" data-content="Learn how to support cardiovascular health through strategic nutrition choices and lifestyle modifications" data-popularity="79">
                        <div class="card-header">
                            <div class="category-badge modules">
                                <i class="fa-solid fa-book-open"></i>
                                Modules
                            </div>
                        </div>
                        <div class="card-image">
                            <img src="../assets/modules/module-1.jpg" alt="Heart Health">
                        </div>
                        <div class="card-content">
                            <h2>Heart-Healthy Nutrition</h2>
                            <p>Learn how to support cardiovascular health through strategic nutrition choices and lifestyle modifications. Evidence-based approaches to heart disease prevention.</p>
                        </div>
                        <div class="card-actions">
                            <a href="./module-detail.php?id=7" class="start-module-btn">
                                Start Learning
                                <i class="fa-solid fa-play"></i>
                            </a>
                        </div>
                    </article>

                    <article class="module-card" data-category="cooking" data-date="2024-02-25" data-title="Advanced Meal Prep Strategies" data-content="Optimize your meal preparation with advanced techniques for batch cooking, food safety, and storage solutions" data-popularity="72">
                        <div class="card-header">
                            <div class="category-badge modules">
                                <i class="fa-solid fa-book-open"></i>
                                Modules
                            </div>
                        </div>
                        <div class="card-image">
                            <img src="../assets/modules/module-2.jpg" alt="Meal Prep">
                        </div>
                        <div class="card-content">
                            <h2>Advanced Meal Prep Strategies</h2>
                            <p>Optimize your meal preparation with advanced techniques for batch cooking, food safety, and storage solutions. Perfect for busy professionals and families.</p>
                        </div>
                        <div class="card-actions">
                            <a href="./module-detail.php?id=8" class="start-module-btn">
                                Start Learning
                                <i class="fa-solid fa-play"></i>
                            </a>
                        </div>
                    </article>
                </div>

                <div class="pagination" id="pagination">
                    <button class="pagination-btn prev" id="prevBtn">
                        <i class="fa-solid fa-chevron-left"></i>
                        Previous
                    </button>
                    <div class="pagination-numbers" id="paginationNumbers">
                        <!-- Pagination numbers will be generated by JavaScript -->
                    </div>
                    <button class="pagination-btn next" id="nextBtn">
                        Next
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </main>

    <?php include_once './footer.php'; ?>

    <script src="../js/navbar.js"></script>
    <script>
        class ModuleManager {
            constructor() {
                this.allModules = [];
                this.filteredModules = [];
                this.currentPage = 1;
                this.itemsPerPage = 6;
                this.init();
            }

            init() {
                this.loadModules();
                this.setupEventListeners();
                this.displayModules();
                this.setupPagination();
            }

            loadModules() {
                const cards = document.querySelectorAll('.module-card');
                this.allModules = Array.from(cards).map(card => ({
                    element: card,
                    category: card.getAttribute('data-category'),
                    date: card.getAttribute('data-date'),
                    title: card.getAttribute('data-title').toLowerCase(),
                    content: card.getAttribute('data-content').toLowerCase(),
                    popularity: parseInt(card.getAttribute('data-popularity'))
                }));
                this.filteredModules = [...this.allModules];
            }

            setupEventListeners() {
                const searchInput = document.getElementById('searchInput');
                searchInput.addEventListener('input', (e) => {
                    this.filterModules();
                });

                const categoryFilter = document.getElementById('categoryFilter');
                categoryFilter.addEventListener('change', (e) => {
                    this.filterModules();
                });

                const sortFilter = document.getElementById('sortFilter');
                sortFilter.addEventListener('change', (e) => {
                    this.sortModules();
                });

                document.getElementById('prevBtn').addEventListener('click', () => {
                    if (this.currentPage > 1) {
                        this.currentPage--;
                        this.displayModules();
                        this.updatePagination();
                    }
                });

                document.getElementById('nextBtn').addEventListener('click', () => {
                    const totalPages = Math.ceil(this.filteredModules.length / this.itemsPerPage);
                    if (this.currentPage < totalPages) {
                        this.currentPage++;
                        this.displayModules();
                        this.updatePagination();
                    }
                });
            }

            filterModules() {
                const searchTerm = document.getElementById('searchInput').value.toLowerCase();
                const selectedCategory = document.getElementById('categoryFilter').value;

                this.filteredModules = this.allModules.filter(module => {
                    const matchesSearch = module.title.includes(searchTerm) || 
                                        module.content.includes(searchTerm);
                    const matchesCategory = selectedCategory === 'all' || 
                                          module.category === selectedCategory;
                    
                    return matchesSearch && matchesCategory;
                });

                this.currentPage = 1;
                this.sortModules();
                this.displayModules();
                this.setupPagination();
            }

            sortModules() {
                const sortOrder = document.getElementById('sortFilter').value;
                
                this.filteredModules.sort((a, b) => {
                    if (sortOrder === 'newest') {
                        return new Date(b.date) - new Date(a.date);
                    } else if (sortOrder === 'oldest') {
                        return new Date(a.date) - new Date(b.date);
                    } else if (sortOrder === 'popular') {
                        return b.popularity - a.popularity;
                    }
                });

                this.displayModules();
            }

            displayModules() {
                const grid = document.getElementById('modulesGrid');
                const startIndex = (this.currentPage - 1) * this.itemsPerPage;
                const endIndex = startIndex + this.itemsPerPage;
                
                this.allModules.forEach(module => {
                    module.element.style.display = 'none';
                });

                this.filteredModules.slice(startIndex, endIndex).forEach(module => {
                    module.element.style.display = 'block';
                });

                if (this.filteredModules.length === 0) {
                    this.showNoResults();
                } else {
                    this.hideNoResults();
                }

                this.updatePagination();
            }

            showNoResults() {
                const grid = document.getElementById('modulesGrid');
                let noResultsMsg = document.getElementById('noResultsMessage');
                
                if (!noResultsMsg) {
                    noResultsMsg = document.createElement('div');
                    noResultsMsg.id = 'noResultsMessage';
                    noResultsMsg.className = 'no-results-message';
                    noResultsMsg.innerHTML = `
                        <div style="text-align: center; padding: 60px 20px; color: #666;">
                            <i class="fa-solid fa-search" style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;"></i>
                            <h3>No modules found</h3>
                            <p>Try adjusting your search terms or filters</p>
                        </div>
                    `;
                    grid.appendChild(noResultsMsg);
                }
                noResultsMsg.style.display = 'block';
            }

            hideNoResults() {
                const noResultsMsg = document.getElementById('noResultsMessage');
                if (noResultsMsg) {
                    noResultsMsg.style.display = 'none';
                }
            }

            setupPagination() {
                const totalPages = Math.ceil(this.filteredModules.length / this.itemsPerPage);
                const paginationNumbers = document.getElementById('paginationNumbers');
                
                paginationNumbers.innerHTML = '';

                if (totalPages <= 1) {
                    document.getElementById('pagination').style.display = 'none';
                    return;
                }

                document.getElementById('pagination').style.display = 'flex';

                // Generate page numbers
                for (let i = 1; i <= Math.min(totalPages, 5); i++) {
                    const pageBtn = document.createElement('button');
                    pageBtn.className = 'pagination-number';
                    pageBtn.textContent = i;
                    
                    if (i === this.currentPage) {
                        pageBtn.classList.add('active');
                    }

                    pageBtn.addEventListener('click', () => {
                        this.currentPage = i;
                        this.displayModules();
                        this.updatePagination();
                    });

                    paginationNumbers.appendChild(pageBtn);
                }

                // Add dots and last page if needed
                if (totalPages > 5) {
                    const dots = document.createElement('span');
                    dots.className = 'pagination-dots';
                    dots.textContent = '...';
                    paginationNumbers.appendChild(dots);

                    const lastPageBtn = document.createElement('button');
                    lastPageBtn.className = 'pagination-number';
                    lastPageBtn.textContent = totalPages;
                    
                    if (totalPages === this.currentPage) {
                        lastPageBtn.classList.add('active');
                    }

                    lastPageBtn.addEventListener('click', () => {
                        this.currentPage = totalPages;
                        this.displayModules();
                        this.updatePagination();
                    });

                    paginationNumbers.appendChild(lastPageBtn);
                }

                this.updatePagination();
            }

            updatePagination() {
                const totalPages = Math.ceil(this.filteredModules.length / this.itemsPerPage);
                const prevBtn = document.getElementById('prevBtn');
                const nextBtn = document.getElementById('nextBtn');

                prevBtn.disabled = this.currentPage === 1;
                nextBtn.disabled = this.currentPage === totalPages || totalPages === 0;

                document.querySelectorAll('.pagination-number').forEach(btn => {
                    btn.classList.remove('active');
                    if (parseInt(btn.textContent) === this.currentPage) {
                        btn.classList.add('active');
                    }
                });

                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            new ModuleManager();
        });
    </script>
</body>

</html>