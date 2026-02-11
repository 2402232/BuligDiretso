// tracking.js

// Placeholder for active emergencies data.
// Replace this with a real fetch/AJAX call to your backend.
const activeEmergencies = [];

function renderTracking() {
    const body = document.getElementById('trackingBody');
    const emptyState = document.getElementById('emptyState');

    if (activeEmergencies.length === 0) {
        // Show empty state
        if (emptyState) emptyState.style.display = 'flex';
        return;
    }

    // Hide empty state and render emergency cards
    if (emptyState) emptyState.style.display = 'none';

    activeEmergencies.forEach(function (em) {
        const card = document.createElement('div');
        card.classList.add('tracking-emergency-item');
        card.innerHTML = `
            <div class="tei-left">
                <span class="tei-id">${em.id}</span>
                <p class="tei-desc">${em.desc}</p>
                <p class="tei-loc"><i class="ri-map-pin-line"></i> ${em.loc}</p>
            </div>
            <div class="tei-right">
                <span class="tei-status ${em.status.toLowerCase()}">${em.status}</span>
            </div>
        `;
        body.appendChild(card);
    });
}

document.addEventListener('DOMContentLoaded', renderTracking);