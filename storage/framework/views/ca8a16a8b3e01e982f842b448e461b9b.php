

<?php $__env->startSection('content'); ?>
<div>
    <h2 class="text-lg font-medium mb-4">Waystone — Shared Thoughts</h2>

    <?php if($quotes->isEmpty() && $notes->isEmpty() && $thoughts->isEmpty()): ?>
        <p class="text-slate-400">There is nothing shared yet.</p>
    <?php endif; ?>

    <?php $__currentLoopData = $quotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $q): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="mb-4">
            <blockquote class="italic"><?php echo e($q->body ?? ''); ?></blockquote>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <?php $__currentLoopData = $notes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $n): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="mb-4">
            <div><?php echo e($n->body ?? ''); ?></div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <?php $__currentLoopData = $thoughts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="mb-4">
            <div class="text-sm text-slate-300"><?php echo e($t->body ?? ''); ?></div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\wamp64\www\MidnightPilgrim\resources\views/waystone/index.blade.php ENDPATH**/ ?>