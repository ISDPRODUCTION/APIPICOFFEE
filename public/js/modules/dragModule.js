/**
 * dragModule.js
 * Enables drag-and-drop for floating panels with viewport clamping.
 * Position is persisted to localStorage via uiStore.
 */

const dragModule = (() => {

    function _getPanelCoords(element) {
        const rect = element.getBoundingClientRect();
        return { left: rect.left, top: rect.top };
    }

    function enableDrag(element, handle) {
        if (!element || !handle || handle.dataset.dragBound === '1') return;
        handle.dataset.dragBound = '1';

        let isDragging = false;
        let startX, startY, initLeft, initTop;

        const onMove = (clientX, clientY) => {
            if (!isDragging) return;

            const dx = clientX - startX;
            const dy = clientY - startY;

            let newLeft = initLeft + dx;
            let newTop  = initTop  + dy;

            const clamped = constrainToViewport(newLeft, newTop, element);
            newLeft = clamped.x;
            newTop  = clamped.y;

            element.style.left   = newLeft + 'px';
            element.style.top    = newTop  + 'px';
            element.style.bottom = 'auto';
            element.style.right  = 'auto';

            uiStore.setPanelPosition(newLeft, newTop);
        };

        const startDrag = (clientX, clientY) => {
            isDragging = true;
            startX  = clientX;
            startY  = clientY;
            const coords = _getPanelCoords(element);
            initLeft = coords.left;
            initTop  = coords.top;
            handle.style.cursor = 'grabbing';
        };

        const endDrag = () => {
            if (!isDragging) return;
            isDragging = false;
            handle.style.cursor = 'grab';
            savePosition(element);
        };

        handle.addEventListener('mousedown', (e) => {
            if (window.innerWidth < 768) return;
            startDrag(e.clientX, e.clientY);
            e.preventDefault();
        });

        document.addEventListener('mousemove', (e) => onMove(e.clientX, e.clientY));

        document.addEventListener('mouseup', endDrag);

        handle.addEventListener('touchstart', (e) => {
            if (window.innerWidth < 768) return;
            const t = e.touches[0];
            startDrag(t.clientX, t.clientY);
            e.preventDefault();
        }, { passive: false });

        document.addEventListener('touchmove', (e) => {
            if (!isDragging) return;
            const t = e.touches[0];
            onMove(t.clientX, t.clientY);
            e.preventDefault();
        }, { passive: false });

        document.addEventListener('touchend', endDrag);
    }

    function constrainToViewport(x, y, element) {
        const vw     = window.innerWidth;
        const vh     = window.innerHeight;
        const width  = element.offsetWidth  || 320;
        const height = element.offsetHeight || 300;

        return {
            x: Math.max(0, Math.min(x, vw - width)),
            y: Math.max(0, Math.min(y, vh - height)),
        };
    }

    function savePosition(element) {
        const pos = { x: element.offsetLeft, y: element.offsetTop };
        uiStore.setPanelPosition(pos.x, pos.y);
        uiStore.savePanelPosition();
    }

    function loadPosition(element) {
        const pos = uiStore.loadPanelPosition();

        if (pos && (pos.x !== 0 || pos.y !== 0)) {
            element.style.left   = pos.x + 'px';
            element.style.top    = pos.y + 'px';
            element.style.bottom = 'auto';
            element.style.right  = 'auto';
        }
    }

    return { enableDrag, constrainToViewport, savePosition, loadPosition };
})();

window.dragModule = dragModule;
