/**
 * Loading Skeleton System
 * Show skeleton loading before content loads
 * Features: Form skeleton, page skeleton, custom skeleton
 */

const LoadingSkeleton = {
	/**
	 * Show page loading skeleton
	 */
	showPage: function () {
		const skeleton = document.createElement("div");
		skeleton.id = "loading-skeleton";
		skeleton.className = "loading-skeleton-page";
		skeleton.innerHTML = `
            <div class="skeleton-container">
                <!-- Header skeleton -->
                <div class="skeleton-header">
                    <div class="skeleton-line skeleton-title"></div>
                    <div class="skeleton-line skeleton-subtitle"></div>
                </div>
                
                <!-- Card skeleton -->
                <div class="skeleton-card">
                    <div class="skeleton-line skeleton-heading"></div>
                    <div class="skeleton-form-grid">
                        <div class="skeleton-form-column">
                            <div class="skeleton-line skeleton-input"></div>
                            <div class="skeleton-line skeleton-input"></div>
                            <div class="skeleton-line skeleton-input"></div>
                        </div>
                        <div class="skeleton-form-column">
                            <div class="skeleton-line skeleton-input"></div>
                            <div class="skeleton-line skeleton-input"></div>
                            <div class="skeleton-line skeleton-input"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Card skeleton -->
                <div class="skeleton-card">
                    <div class="skeleton-line skeleton-heading"></div>
                    <div class="skeleton-form-grid">
                        <div class="skeleton-form-column">
                            <div class="skeleton-line skeleton-input"></div>
                            <div class="skeleton-line skeleton-input"></div>
                        </div>
                        <div class="skeleton-form-column">
                            <div class="skeleton-line skeleton-input"></div>
                            <div class="skeleton-line skeleton-input"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Button skeleton -->
                <div class="skeleton-buttons">
                    <div class="skeleton-line skeleton-button"></div>
                    <div class="skeleton-line skeleton-button"></div>
                </div>
            </div>
        `;

		document.body.appendChild(skeleton);

		// Trigger animation
		setTimeout(() => {
			skeleton.classList.add("show");
		}, 10);
	},

	/**
	 * Show form loading skeleton
	 */
	showForm: function () {
		const skeleton = document.createElement("div");
		skeleton.id = "loading-skeleton";
		skeleton.className = "loading-skeleton-overlay";
		skeleton.innerHTML = `
            <div class="skeleton-spinner-container">
                <div class="skeleton-spinner"></div>
                <div class="skeleton-text">Memuat data...</div>
            </div>
        `;

		document.body.appendChild(skeleton);

		setTimeout(() => {
			skeleton.classList.add("show");
		}, 10);
	},

	/**
	 * Show saving skeleton (when submitting form)
	 */
	showSaving: function (message = "Menyimpan data...") {
		const skeleton = document.createElement("div");
		skeleton.id = "loading-skeleton";
		skeleton.className = "loading-skeleton-overlay";
		skeleton.innerHTML = `
            <div class="skeleton-spinner-container">
                <div class="skeleton-spinner saving"></div>
                <div class="skeleton-text">${message}</div>
                <div class="skeleton-subtext">Mohon tunggu sebentar</div>
            </div>
        `;

		document.body.appendChild(skeleton);

		setTimeout(() => {
			skeleton.classList.add("show");
		}, 10);
	},

	/**
	 * Hide loading skeleton
	 */
	hide: function () {
		const skeleton = document.getElementById("loading-skeleton");
		if (skeleton) {
			skeleton.classList.remove("show");
			skeleton.classList.add("hide");
			setTimeout(() => {
				skeleton.remove();
			}, 300);
		}
	},

	/**
	 * Show skeleton then hide after duration
	 */
	showThenHide: function (type = "form", duration = 1000) {
		if (type === "page") {
			this.showPage();
		} else if (type === "saving") {
			this.showSaving();
		} else {
			this.showForm();
		}

		setTimeout(() => {
			this.hide();
		}, duration);
	},
};

// Make it globally available FIRST
window.LoadingSkeleton = LoadingSkeleton;

// Auto-show skeleton on page load
if (document.readyState === "loading") {
	document.addEventListener("DOMContentLoaded", function () {
		console.log("[LoadingSkeleton] DOMContentLoaded - checking attributes");
		// Check if page has data-loading attribute
		const body = document.body;
		if (body.hasAttribute("data-show-skeleton")) {
			const type = body.getAttribute("data-skeleton-type") || "page";
			const duration =
				parseInt(body.getAttribute("data-skeleton-duration")) || 800;

			LoadingSkeleton.showThenHide(type, duration);
		}
	});
} else {
	// DOM already loaded
	console.log("[LoadingSkeleton] DOM already loaded");
}

// Intercept form submissions to show saving skeleton
document.addEventListener(
	"submit",
	function (e) {
		const form = e.target;

		// Only process if target is actually a FORM element
		if (form.tagName !== "FORM") {
			console.log(
				"[LoadingSkeleton] Submit event from non-form element, skipping:",
				form.tagName
			);
			return;
		}

		console.log(
			"[LoadingSkeleton] Form submit detected:",
			form.id || form.name || "unnamed form"
		);

		// Check if form has data-no-loading attribute
		if (form.hasAttribute("data-no-loading")) {
			console.log("[LoadingSkeleton] Form has data-no-loading, skipping");
			return;
		}

		// Show saving skeleton
		console.log("[LoadingSkeleton] Showing saving skeleton");
		LoadingSkeleton.showSaving();
	},
	true
); // Use capture phase to ensure it runs first
