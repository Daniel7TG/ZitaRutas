<script>
    document.addEventListener("DOMContentLoaded", function() {
        const sheet = document.getElementById('routesBottomSheet');
        const handle = document.getElementById('dragHandle');
        if (!sheet || !handle) return;

        let isDragging = false;
        let startY = 0;
        let currentTranslateY = 0;
        let sheetHeight = 0;
        let isCollapsed = false;

        function getSheetHeight() {
            return sheet.offsetHeight;
        }

        function collapse() {
            isCollapsed = true;
            sheet.style.transition = 'transform 0.35s cubic-bezier(0.4, 0, 0.2, 1)';
            sheet.classList.add('collapsed');
            sheet.style.transform = '';
            currentTranslateY = 0;
            // Redimensionar el mapa para que ocupe el espacio libre
            if (typeof window.map !== 'undefined' && window.map) {
                setTimeout(function() {
                    window.map.invalidateSize();
                }, 400);
            }
        }

        function expand() {
            isCollapsed = false;
            sheet.style.transition = 'transform 0.35s cubic-bezier(0.4, 0, 0.2, 1)';
            sheet.classList.remove('collapsed');
            sheet.style.transform = '';
            currentTranslateY = 0;
            if (typeof window.map !== 'undefined' && window.map) {
                setTimeout(function() {
                    window.map.invalidateSize();
                }, 400);
            }
        }

        function onStart(clientY) {
            isDragging = true;
            startY = clientY;
            sheetHeight = getSheetHeight();
            sheet.style.transition = 'none';
        }

        function onMove(clientY) {
            if (!isDragging) return;
            const deltaY = clientY - startY;

            if (isCollapsed) {
                // Si está colapsado, solo permitir arrastrar hacia arriba (deltaY negativo)
                const maxCollapse = sheetHeight - 36;
                const translateY = Math.max(0, Math.min(maxCollapse, maxCollapse + deltaY));
                sheet.style.transform = `translateY(${translateY}px)`;
            } else {
                // Si está expandido, solo permitir arrastrar hacia abajo (deltaY positivo)
                const translateY = Math.max(0, deltaY);
                sheet.style.transform = `translateY(${translateY}px)`;
            }
        }

        function onEnd(clientY) {
            if (!isDragging) return;
            isDragging = false;
            const deltaY = clientY - startY;
            const threshold = sheetHeight * 0.3;

            if (isCollapsed) {
                // Si está colapsado y arrastra suficiente hacia arriba → expandir
                if (deltaY < -threshold) {
                    expand();
                } else {
                    collapse();
                }
            } else {
                // Si está expandido y arrastra suficiente hacia abajo → colapsar
                if (deltaY > threshold) {
                    collapse();
                } else {
                    expand();
                }
            }
        }

        // ── Touch Events ──
        handle.addEventListener('touchstart', function(e) {
            onStart(e.touches[0].clientY);
        }, {
            passive: true
        });

        document.addEventListener('touchmove', function(e) {
            if (isDragging) onMove(e.touches[0].clientY);
        }, {
            passive: true
        });

        document.addEventListener('touchend', function(e) {
            if (isDragging) onEnd(e.changedTouches[0].clientY);
        });

        // ── Mouse Events ──
        handle.addEventListener('mousedown', function(e) {
            e.preventDefault();
            onStart(e.clientY);
        });

        document.addEventListener('mousemove', function(e) {
            if (isDragging) onMove(e.clientY);
        });

        document.addEventListener('mouseup', function(e) {
            if (isDragging) onEnd(e.clientY);
        });

        // ── Click toggle ──
        handle.addEventListener('click', function() {
            if (isCollapsed) {
                expand();
            } else {
                collapse();
            }
        });
    });
</script>
