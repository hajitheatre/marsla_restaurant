<div class="modal onboarding-modal-hardened" id="onboardingModal" style="display: none; z-index: 10000;">
    <div class="modal-content onboarding-light-mode">
        <div class="modal-header" style="flex-direction: column; align-items: center; gap: 1rem; border-bottom: none !important;">
            <img src="../assets/Logo_Column.svg" alt="Marsla Logo" style="height: 80px; width: auto;">
            <h3 class="modal-title" style="text-align: center; width: 100%;">Complete Your Profile</h3>
        </div>
        <div class="modal-body">
            <p style="margin-bottom: 2rem; color: #64748b; text-align: center; font-size: 0.95rem; line-height: 1.5;">Welcome to Marsla! Please fill in these final details to unlock your dashboard.</p>
            <form id="onboardingForm" onsubmit="handleOnboardingSubmit(event)" style="display: flex; flex-direction: column; gap: 1.5rem;">
                <div class="form-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
                    <div class="form-group">
                        <label for="onboardFirstName" style="color: #475569; font-weight: 500;">First Name</label>
                        <input type="text" id="onboardFirstName" class="form-input" placeholder=" " required 
                               style="background: #f8fafc; border: 1px solid #e2e8f0; color: #1e293b; padding: 0.75rem 1rem; border-radius: 0.75rem;">
                    </div>
                    <div class="form-group">
                        <label for="onboardLastName" style="color: #475569; font-weight: 500;">Last Name</label>
                        <input type="text" id="onboardLastName" class="form-input" placeholder=" " required
                               style="background: #f8fafc; border: 1px solid #e2e8f0; color: #1e293b; padding: 0.75rem 1rem; border-radius: 0.75rem;">
                    </div>
                </div>
                <div class="form-group">
                    <label for="onboardPhone" style="color: #475569; font-weight: 500;">Phone Number</label>
                    <input type="tel" id="onboardPhone" class="form-input" placeholder=" " required
                           style="background: #f8fafc; border: 1px solid #e2e8f0; color: #1e293b; padding: 0.75rem 1rem; border-radius: 0.75rem;">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 1rem; font-weight: 600; border-radius: 1rem; margin-top: 0.5rem; background: #22c55e; color: white;">
                    Complete Setup & Unlock
                </button>
            </form>
        </div>
    </div>
</div>

<style>
/* True Takeover Modal Styles - Hardened & Light Mode Forced */
.onboarding-modal-hardened {
    position: fixed;
    inset: 0;
    background: rgba(10, 10, 10, 0.98) !important;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
    backdrop-filter: blur(10px);
    pointer-events: all !important;
    z-index: 99999 !important;
}

.onboarding-light-mode {
    background: #ffffff !important;
    color: #1e293b !important;
    border-radius: 2rem !important; 
    padding: 1rem !important;
    max-width: 420px !important; /* Reduced width */
    width: 100%;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important;
    border: none !important;
}

.onboarding-light-mode .modal-header {
    padding: 2rem 2rem 1rem 2rem !important;
}

.onboarding-light-mode .modal-title {
    color: #0f172a !important;
    font-size: 1.5rem !important;
    font-weight: 700 !important;
}

.onboarding-light-mode .modal-body {
    padding: 1.5rem 2rem 2.5rem 2rem !important;
}

.onboarding-modal-hardened .modal-content {
    animation: modalBounceIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes modalBounceIn {
    0% { transform: scale(0.9); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}
</style>
