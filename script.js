// sample data for campaigns and events
const sampleCampaigns = [
    {
        id: 1,
        title: "Scholarship Program for Youth",
        description: "Supporting underprivileged youth and children with educational scholarships, school supplies, and learning materials.",
        goal: 500000,
        raised: 325000,
        category: "Education",
        image: "education"
    },
    {
        id: 2,
        title: "Shelter & Housing Assistance",
        description: "Providing safe shelter and housing assistance for families in need, particularly those affected by disasters.",
        goal: 750000,
        raised: 450000,
        category: "Shelter",
        image: "shelter"
    },
    {
        id: 3,
        title: "Clothing & Basic Needs Drive",
        description: "Distributing clothing, food, and essential items to families and individuals in Quezon City communities.",
        goal: 300000,
        raised: 195000,
        category: "Welfare",
        image: "welfare"
    },
    {
        id: 4,
        title: "Emergency Relief Fund",
        description: "Emergency assistance for communities affected by natural disasters and crises.",
        goal: 1000000,
        raised: 680000,
        category: "Disaster Relief",
        image: "disaster"
    }
];

const sampleEvents = [
    {
        id: 1,
        title: "Community Outreach Program",
        date: new Date(2026, 1, 15),
        description: "Join us for a day of community service and engagement in Quezon City.",
        location: "Quezon City",
        type: "community",
        contactPerson: "AMF Secretariat (info@albanfoundation.org)"
    },
    {
        id: 2,
        title: "Scholarship Awarding Ceremony",
        date: new Date(2026, 2, 20),
        description: "Annual ceremony recognizing scholarship recipients and their achievements.",
        location: "Quezon City",
        type: "ceremony",
        contactPerson: "Scholarship Desk (info@albanfoundation.org)"
    },
    {
        id: 3,
        title: "Volunteer Training Session",
        date: new Date(2026, 1, 28),
        description: "Training session for new volunteers interested in supporting our foundation.",
        location: "Quezon City",
        type: "training",
        contactPerson: "Volunteer Coordinator (info@albanfoundation.org)"
    }
];

// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.getElementById('hamburger');
    const navMenu = document.getElementById('navMenu');

    if (hamburger && navMenu) {
        hamburger.addEventListener('click', function() {
            navMenu.classList.toggle('active');
        });
    }

    // Load featured campaigns on homepage
    loadFeaturedCampaigns();
    loadUpcomingEvents();
});

// Load featured campaigns
function loadFeaturedCampaigns() {
    const container = document.getElementById('featuredCampaigns');
    if (!container) return;

    container.innerHTML = sampleCampaigns.slice(0, 3).map(campaign => `
        <div class="campaign-card">
            <div class="campaign-image" style="background: linear-gradient(135deg, #2563eb, #10b981);"></div>
            <div class="campaign-content">
                <h3 class="campaign-title">${campaign.title}</h3>
                <p class="campaign-description">${campaign.description}</p>
                <div class="campaign-progress">
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: ${Math.min((campaign.raised / campaign.goal) * 100, 100)}%"></div>
                    </div>
                    <div class="progress-info">
                        <span>₱${campaign.raised.toLocaleString()}</span>
                        <span class="campaign-goal">Goal: ₱${campaign.goal.toLocaleString()}</span>
                    </div>
                </div>
                <div class="campaign-footer">
                    <a href="donate.html" class="btn btn-primary">Donate Now</a>
                    <a href="campaign-details.html?id=${campaign.id}" class="btn btn-outline">View Details</a>
                </div>
            </div>
        </div>
    `).join('');
}

// Load upcoming events
function loadUpcomingEvents() {
    const container = document.getElementById('upcomingEvents');
    if (!container) return;

    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    container.innerHTML = sampleEvents.map(event => {
        const date = event.date;
        return `
            <div class="event-card">
                <div class="event-date">
                    <div class="event-day">${date.getDate()}</div>
                    <div class="event-month">${months[date.getMonth()]}</div>
                </div>
                <div class="event-details">
                    <h3>${event.title}</h3>
                    <p>${event.description}</p>
                    <p style="margin-top: 0.5rem; font-size: 0.85rem; color: var(--primary-color);">
                        <i class="fas fa-map-marker-alt"></i> ${event.location}
                    </p>
                </div>
            </div>
        `;
    }).join('');
}

// Form validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;

    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.style.borderColor = '#ef4444';
        } else {
            input.style.borderColor = '#e5e7eb';
        }
    });

    return isValid;
}

// Donation form handler
function handleDonation(event) {
    event.preventDefault();
    
    if (validateForm('donationForm')) {
        const formData = new FormData(event.target);
        const donationData = {
            amount: formData.get('amount'),
            method: formData.get('paymentMethod'),
            campaignId: formData.get('campaignId'),
            anonymous: formData.get('anonymous') === 'on'
        };

        // Simulate donation processing
        alert('Thank you for your donation to Alban Memorial Foundation Inc! You will be redirected to the payment gateway.');
        // In a real application, this would redirect to payment gateway
    }
}

// Login form handler

// Register form handler


