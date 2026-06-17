<?php
require_once 'includes/db.php';
require_once 'includes/auth_helpers.php';

requireLogin();

// Handle status updates and deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $listing_id = $_POST['listing_id'] ?? 0;
    $action = $_POST['action'] ?? '';
    
    // Verify ownership
    $stmt = $pdo->prepare("SELECT id FROM listings WHERE id = ? AND user_id = ?");
    $stmt->execute([$listing_id, $_SESSION['user_id']]);
    if ($stmt->fetch()) {
        if ($action === 'delete') {
            $pdo->prepare("DELETE FROM listings WHERE id = ?")->execute([$listing_id]);
        } elseif ($action === 'mark_sold') {
            $pdo->prepare("UPDATE listings SET status = 'sold' WHERE id = ?")->execute([$listing_id]);
        } elseif ($action === 'mark_active') {
            $pdo->prepare("UPDATE listings SET status = 'active' WHERE id = ?")->execute([$listing_id]);
        }
    }
    header("Location: my_listings.php");
    exit;
}

// Fetch user listings
$stmt = $pdo->prepare("SELECT l.*, c.name as category_name, 
                       (SELECT image_path FROM listing_images WHERE listing_id = l.id LIMIT 1) as main_image 
                       FROM listings l 
                       JOIN categories c ON l.category_id = c.id 
                       WHERE l.user_id = ? 
                       ORDER BY l.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$listings = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="container mx-auto px-6 py-12">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-8 border-b border-[var(--border-color)] pb-4">
        <h2 class="font-dh text-4xl md:text-5xl uppercase tracking-normal m-0">My Listings</h2>
        <a href="create_listing.php" class="font-am text-sm text-black bg-white px-4 py-2 mt-4 md:mt-0 uppercase font-bold tracking-wider rounded hover:bg-gray-200 transition-colors">
            + New Listing
        </a>
    </div>

    <?php if(isset($_GET['success'])): ?>
        <div class="bg-green-900/30 border border-green-700/50 text-green-200 px-4 py-3 rounded mb-8 font-cgr">
            Action completed successfully.
        </div>
    <?php endif; ?>

    <div class="space-y-6">
        <?php foreach($listings as $listing): ?>
            <div class="glass-panel rounded-[var(--radius)] overflow-hidden border border-[var(--border-color)] flex flex-col md:flex-row">
                <div class="md:w-1/4 h-48 md:h-auto border-b md:border-b-0 md:border-r border-[var(--border-color)]">
                    <?php if($listing['main_image']): ?>
                        <img src="assets/uploads/<?php echo htmlspecialchars($listing['main_image']); ?>" class="w-full h-full object-cover" alt="Image">
                    <?php else: ?>
                        <div class="w-full h-full bg-[var(--c3)] flex items-center justify-center text-[var(--c2)] font-am text-xs uppercase tracking-widest p-4 text-center">
                            <span>No Image</span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="p-6 md:w-3/4 flex flex-col justify-between">
                    <div>
                        <div class="flex flex-wrap justify-between items-start gap-4 mb-2">
                            <h5 class="font-dh text-2xl uppercase tracking-normal m-0">
                                <?php echo htmlspecialchars($listing['title']); ?>
                            </h5>
                            <?php 
                                $status_color = $listing['status'] === 'active' ? 'bg-green-500 text-white' : ($listing['status'] === 'sold' ? 'bg-yellow-500 text-black' : 'bg-red-500 text-white'); 
                            ?>
                            <span class="px-3 py-1 font-am text-xs uppercase tracking-widest font-bold <?php echo $status_color; ?> rounded">
                                <?php echo strtoupper($listing['status']); ?>
                            </span>
                        </div>
                        <p class="font-am text-xl text-[var(--primary-color)] mb-3">R <?php echo number_format($listing['price'], 2); ?></p>
                        
                        <div class="flex flex-wrap gap-3 font-am text-xs text-[var(--c2)] uppercase tracking-wider mb-6">
                            <span>[<?php echo htmlspecialchars($listing['category_name']); ?>]</span>
                            <span>|</span>
                            <span><?php echo date('Y-m-d', strtotime($listing['created_at'])); ?></span>
                        </div>
                    </div>
                    
                    <div class="flex flex-wrap gap-3 pt-4 border-t border-[var(--border-color)]">
                        <a href="listing.php?id=<?php echo $listing['id']; ?>" class="px-4 py-2 border border-[var(--border-color)] text-white font-am text-xs uppercase tracking-wider hover:bg-[var(--c3)] transition-colors rounded">
                            View
                        </a>
                        
                        <?php if($listing['status'] === 'active'): ?>
                            <form method="POST" action="" class="inline">
                                <input type="hidden" name="listing_id" value="<?php echo $listing['id']; ?>">
                                <input type="hidden" name="action" value="mark_sold">
                                <button type="submit" class="px-4 py-2 border border-yellow-600/50 text-yellow-500 hover:bg-yellow-600/20 font-am text-xs uppercase tracking-wider transition-colors rounded">
                                    Mark as Sold
                                </button>
                            </form>
                        <?php elseif($listing['status'] === 'sold'): ?>
                            <form method="POST" action="" class="inline">
                                <input type="hidden" name="listing_id" value="<?php echo $listing['id']; ?>">
                                <input type="hidden" name="action" value="mark_active">
                                <button type="submit" class="px-4 py-2 border border-green-600/50 text-green-500 hover:bg-green-600/20 font-am text-xs uppercase tracking-wider transition-colors rounded">
                                    Mark as Active
                                </button>
                            </form>
                        <?php endif; ?>
                        
                        <form method="POST" action="" class="inline ml-auto">
                            <input type="hidden" name="listing_id" value="<?php echo $listing['id']; ?>">
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" class="px-4 py-2 border border-red-900/50 text-red-500 hover:bg-red-900/30 hover:text-red-400 font-am text-xs uppercase tracking-wider transition-colors rounded" onclick="return confirm('Are you sure you want to delete this listing?');">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if(empty($listings)): ?>
            <div class="glass-panel p-12 text-center text-[var(--c2)] font-cgr rounded-[var(--radius)]">
                You haven't created any listings yet. <br>
                <a href="create_listing.php" class="text-white hover:text-[var(--primary-color)] underline mt-2 inline-block">Create one now</a>.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
