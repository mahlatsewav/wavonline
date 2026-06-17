<?php
require_once 'includes/db.php';
require_once 'includes/auth_helpers.php';

$category_filter = $_GET['category'] ?? '';
$search_query = $_GET['search'] ?? '';
$province_filter = $_GET['province'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';
$sort = $_GET['sort'] ?? 'recent';

// Fetch all categories for filter
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll();

// Build query
$query = "SELECT l.*, c.name as category_name, 
          (SELECT image_path FROM listing_images WHERE listing_id = l.id LIMIT 1) as main_image 
          FROM listings l 
          JOIN categories c ON l.category_id = c.id 
          WHERE l.status = 'active'";
$params = [];

if ($category_filter) {
    $query .= " AND l.category_id = ?";
    $params[] = $category_filter;
}

if ($province_filter) {
    $query .= " AND l.province = ?";
    $params[] = $province_filter;
}

if ($search_query) {
    $query .= " AND (l.title LIKE ? OR l.description LIKE ? OR l.city LIKE ? OR l.suburb LIKE ?)";
    $search_term = "%$search_query%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

if ($min_price !== '') {
    $query .= " AND l.price >= ?";
    $params[] = $min_price;
}

if ($max_price !== '') {
    $query .= " AND l.price <= ?";
    $params[] = $max_price;
}

if ($sort === 'price_asc') {
    $query .= " ORDER BY l.price ASC";
} elseif ($sort === 'price_desc') {
    $query .= " ORDER BY l.price DESC";
} else {
    $query .= " ORDER BY l.created_at DESC";
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$listings = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="container mx-auto px-6 py-12">
    <div class="flex flex-col lg:flex-row gap-12">
        <!-- Filters -->
        <div class="lg:w-1/4 mb-8 lg:mb-0">
            <div class="glass-panel p-6 rounded-[var(--radius)] lg:sticky top-24">
                <div class="flex justify-between items-center cursor-pointer lg:cursor-default" id="filter-toggle-btn">
                    <h5 class="font-dh text-2xl uppercase tracking-normal m-0">Filters</h5>
                    <button type="button" class="lg:hidden text-[var(--c2)] hover:text-white focus:outline-none">
                        <svg id="filter-icon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                </div>
                <div id="filter-content" class="hidden lg:block mt-6">
                    <form method="GET" action="browse.php" class="space-y-6">
                    <div>
                        <label class="block font-am text-xs text-[var(--c2)] uppercase tracking-wider mb-2">Search</label>
                        <input type="text" name="search" class="w-full bg-[var(--c1)] border border-[var(--border-color)] text-white font-cgr rounded-[var(--radius)] px-4 py-3 focus:outline-none focus:border-[var(--c2)] transition-colors" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Keywords...">
                    </div>
                    <div>
                        <label class="block font-am text-xs text-[var(--c2)] uppercase tracking-wider mb-2">Category</label>
                        <select name="category" class="w-full bg-[var(--c1)] border border-[var(--border-color)] text-white font-cgr rounded-[var(--radius)] px-4 py-3 focus:outline-none focus:border-[var(--c2)] transition-colors appearance-none">
                            <option value="">All Categories</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block font-am text-xs text-[var(--c2)] uppercase tracking-wider mb-2">Province</label>
                        <select name="province" class="w-full bg-[var(--c1)] border border-[var(--border-color)] text-white font-cgr rounded-[var(--radius)] px-4 py-3 focus:outline-none focus:border-[var(--c2)] transition-colors appearance-none">
                            <option value="">All Provinces</option>
                            <?php 
                            $provinces = ['Eastern Cape', 'Free State', 'Gauteng', 'KwaZulu-Natal', 'Limpopo', 'Mpumalanga', 'Northern Cape', 'North West', 'Western Cape'];
                            foreach($provinces as $prov): 
                            ?>
                                <option value="<?php echo $prov; ?>" <?php echo $province_filter == $prov ? 'selected' : ''; ?>>
                                    <?php echo $prov; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-am text-xs text-[var(--c2)] uppercase tracking-wider mb-2">Min Price</label>
                            <input type="number" step="0.01" min="0" name="min_price" class="w-full bg-[var(--c1)] border border-[var(--border-color)] text-white font-cgr rounded-[var(--radius)] px-4 py-3 focus:outline-none focus:border-[var(--c2)] transition-colors" value="<?php echo htmlspecialchars($min_price); ?>" placeholder="0.00">
                        </div>
                        <div>
                            <label class="block font-am text-xs text-[var(--c2)] uppercase tracking-wider mb-2">Max Price</label>
                            <input type="number" step="0.01" min="0" name="max_price" class="w-full bg-[var(--c1)] border border-[var(--border-color)] text-white font-cgr rounded-[var(--radius)] px-4 py-3 focus:outline-none focus:border-[var(--c2)] transition-colors" value="<?php echo htmlspecialchars($max_price); ?>" placeholder="Any">
                        </div>
                    </div>
                    <div>
                        <label class="block font-am text-xs text-[var(--c2)] uppercase tracking-wider mb-2">Sort By</label>
                        <select name="sort" class="w-full bg-[var(--c1)] border border-[var(--border-color)] text-white font-cgr rounded-[var(--radius)] px-4 py-3 focus:outline-none focus:border-[var(--c2)] transition-colors appearance-none">
                            <option value="recent" <?php echo $sort == 'recent' ? 'selected' : ''; ?>>Recently Added</option>
                            <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-white text-black font-cgr font-bold uppercase tracking-wider rounded-[var(--radius)] px-4 py-3 hover:bg-gray-200 transition-colors">Apply Filters</button>
                    
                    <?php if($category_filter || $search_query || $province_filter || $min_price !== '' || $max_price !== '' || $sort !== 'recent'): ?>
                        <a href="browse.php" class="block w-full text-center border border-[var(--border-color)] text-[var(--c2)] font-cgr font-bold uppercase tracking-wider rounded-[var(--radius)] px-4 py-3 hover:bg-[var(--c3)] hover:text-white transition-colors">Clear</a>
                    <?php endif; ?>
                </form>
                </div>
            </div>
        </div>
        
        <!-- Listings Grid -->
        <div class="lg:w-3/4">
            <h2 class="font-dh text-4xl md:text-5xl uppercase tracking-normal mb-8 border-b border-[var(--border-color)] pb-4">Browse Listings</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <?php foreach($listings as $listing): ?>
                    <a href="listing.php?id=<?php echo $listing['id']; ?>" class="block group">
                        <div class="listing-card flex flex-col h-full bg-[var(--c1)]">
                            <div class="relative h-64 overflow-hidden border-b border-[var(--border-color)]">
                                <?php if($listing['main_image']): ?>
                                    <img src="assets/uploads/<?php echo htmlspecialchars($listing['main_image']); ?>" alt="Listing Image" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                                <?php else: ?>
                                    <div class="w-full h-full bg-[var(--c3)] flex items-center justify-center text-[var(--c2)] font-am text-xs uppercase tracking-widest">
                                        <span>No Image</span>
                                    </div>
                                <?php endif; ?>
                                <div class="absolute top-4 right-4 bg-black/50 backdrop-blur px-3 py-1 rounded-full border border-white/10 font-am text-xs text-white uppercase tracking-wider">
                                    R <?php echo number_format($listing['price'], 2); ?>
                                </div>
                            </div>
                            
                            <div class="p-6 flex-1 flex flex-col justify-between">
                                <div>
                                    <h5 class="font-dh text-2xl uppercase tracking-normal mb-2 group-hover:text-[var(--primary-color)] transition-colors">
                                        <?php echo htmlspecialchars($listing['title']); ?>
                                    </h5>
                                    <div class="flex items-center gap-2 font-am text-xs text-[var(--c2)] uppercase tracking-wider mb-1">
                                        <span>[<?php echo htmlspecialchars($listing['category_name']); ?>]</span>
                                    </div>
                                </div>
                                
                                <div class="mt-6 pt-4 border-t border-[var(--border-color)] flex justify-between items-center font-cgr text-sm text-[var(--c2)]">
                                    <span class="opacity-70"><i class="bi bi-geo-alt hidden"></i><?php echo htmlspecialchars($listing['city'] . ', ' . $listing['province']); ?></span>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
                
                <?php if(empty($listings)): ?>
                    <div class="col-span-full">
                        <div class="glass-panel p-12 text-center text-[var(--c2)] font-cgr rounded-[var(--radius)]">
                            No listings found matching your criteria.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('filter-toggle-btn').addEventListener('click', function() {
    if (window.innerWidth < 1024) {
        var content = document.getElementById('filter-content');
        var icon = document.getElementById('filter-icon');
        if (content.classList.contains('hidden')) {
            content.classList.remove('hidden');
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>';
        } else {
            content.classList.add('hidden');
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>';
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>
