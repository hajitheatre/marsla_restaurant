/**
 * Onboarding Management - Hardened Takeover Edition
 */
document.addEventListener('DOMContentLoaded', () => {
    const user = window.currentUser;

    if (user && parseInt(user.onboarded) === 0) {
        showOnboardingModal();
    }
});

function showOnboardingModal() {
    const modal = document.getElementById('onboardingModal');
    if (!modal) return;

    const user = window.currentUser;
    if (user) {
        if (document.getElementById('onboardFirstName')) {
            document.getElementById('onboardFirstName').value = user.first_name || '';
        }
        if (document.getElementById('onboardLastName')) {
            document.getElementById('onboardLastName').value = user.last_name || '';
        }
    }

    // Explicitly show and block closing
    modal.style.display = 'flex';
    modal.classList.add('active'); // Added to trigger any 'active' related styles if needed

    // Hardened Blockers: prevent closing via Escape key or Backdrop click
    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.style.display === 'flex') {
            e.preventDefault();
            e.stopPropagation();
        }
    }, true);

    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            e.preventDefault();
            e.stopPropagation();
            // Optional: Shake animation to indicate it's required
            const content = modal.querySelector('.modal-content');
            content.style.animation = 'none';
            void content.offsetWidth;
            content.style.animation = 'modalBounceIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1), shake 0.4s';
        }
    }, true);
}

async function handleOnboardingSubmit(event) {
    event.preventDefault();

    const firstName = document.getElementById('onboardFirstName').value.trim();
    const lastName = document.getElementById('onboardLastName').value.trim();
    const phone = document.getElementById('onboardPhone').value.trim();

    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner"></span> Saving Profile...';

    try {
        const response = await fetch('../api/profile/complete_onboarding.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                first_name: firstName,
                last_name: lastName,
                phone: phone
            })
        });

        const data = await response.json();

        if (data.success) {
            submitBtn.innerHTML = 'Account Unlocked!';
            submitBtn.style.background = 'var(--success)';

            // Wait slightly before reload to show success state
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            alert(data.message || 'Failed to complete profile');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Server error. Please try again.');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

// Add shake animation to the style tag via JS for robustness
const style = document.createElement('style');
style.textContent = `
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }
    .spinner {
        display: inline-block;
        width: 1rem;
        height: 1rem;
        border: 2px solid rgba(255,255,255,.3);
        border-radius: 50%;
        border-top-color: #fff;
        animation: spin 1s ease-in-out infinite;
        margin-right: 8px;
        vertical-align: middle;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
`;
document.head.appendChild(style);
