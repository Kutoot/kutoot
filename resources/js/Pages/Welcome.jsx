import { Head, Link } from '@inertiajs/react';
import ApplicationLogo from '@/Components/ApplicationLogo';

export default function Welcome({ auth }) {
    return (
        <>
            <Head title="Welcome to Kutoot" />
            <div className="min-h-screen bg-gradient-to-br from-lucky-50 via-white to-ticket-50 overflow-hidden relative">
                {/* Floating decorations */}
                <div className="absolute top-10 left-10 w-16 h-16 bg-lucky-200 rounded-full opacity-20 animate-float" />
                <div className="absolute top-32 right-20 w-10 h-10 bg-ticket-200 rounded-full opacity-20 animate-float" style={{ animationDelay: '0.5s' }} />
                <div className="absolute bottom-20 left-1/4 w-12 h-12 bg-yellow-200 rounded-full opacity-20 animate-float" style={{ animationDelay: '1s' }} />
                <div className="absolute top-1/2 right-10 w-8 h-8 bg-green-200 rounded-full opacity-20 animate-float" style={{ animationDelay: '1.5s' }} />
                <div className="absolute bottom-40 right-1/3 w-6 h-6 bg-prize-200 rounded-full opacity-20 animate-float" style={{ animationDelay: '2s' }} />

                {/* Nav */}
                <nav className="relative z-10 flex items-center justify-between px-6 py-4 max-w-7xl mx-auto">
                    <ApplicationLogo />
                    <div className="flex items-center gap-3">
                        {auth.user ? (
                            <Link
                                href={route('dashboard')}
                                className="rounded-full px-5 py-2.5 font-bold text-sm text-white lucky-gradient shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all"
                            >
                                Dashboard
                            </Link>
                        ) : (
                            <Link
                                href={route('login')}
                                className="rounded-full px-5 py-2.5 font-bold text-sm text-lucky-700 border-2 border-lucky-300 hover:bg-lucky-50 transition-colors"
                            >
                                Login / Register
                            </Link>
                        )}
                    </div>
                </nav>

                {/* Hero */}
                <main className="relative z-10 flex flex-col items-center justify-center px-6 pt-8 sm:pt-12 pb-16 sm:pb-24">
                    {/* Lucky draw wheel decoration */}
                    <div className="relative mb-6">
                        <div className="w-28 h-28 sm:w-32 sm:h-32 starburst opacity-20 animate-spin-slow" />
                        <div className="absolute inset-0 flex items-center justify-center">
                            <div className="w-16 h-16 sm:w-20 sm:h-20 bg-white rounded-full shadow-xl flex items-center justify-center animate-pulse-glow">
                                <span className="text-3xl">🎟️</span>
                            </div>
                        </div>
                    </div>

                    <h1 className="font-display text-4xl sm:text-5xl md:text-7xl text-center bg-gradient-to-r from-lucky-600 via-ticket-500 to-lucky-600 bg-clip-text text-transparent mb-3 leading-tight">
                        Win Big with Kutoot!
                    </h1>
                    <p className="text-base sm:text-lg md:text-xl text-gray-500 text-center max-w-xl mb-4">
                        Your favourite merchants, exclusive discounts, and lucky rewards — all in one place.
                    </p>
                    <p className="text-sm text-gray-400 text-center max-w-md mb-8">
                        Collect stamps with every purchase, redeem discount coupons, and stand a chance to win big in campaigns!
                    </p>

                    {/* CTA Buttons */}
                    <div className="flex flex-col sm:flex-row justify-center gap-3 sm:gap-4 mb-14 w-full sm:w-auto px-4 sm:px-0">
                        {!auth.user && (
                            <Link
                                href={route('login')}
                                className="group relative inline-flex items-center justify-center gap-2 rounded-full px-8 py-4 font-bold text-base sm:text-lg text-white lucky-gradient shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all"
                            >
                                <span className="text-xl sm:text-2xl group-hover:animate-bounce">🎰</span>
                                Start Winning
                                <span className="absolute -top-2 -right-2 golden-badge text-xs px-2 py-0.5 rounded-full">FREE</span>
                            </Link>
                        )}
                        <Link
                            href={auth.user ? route('campaigns.index') : route('login')}
                            className="inline-flex items-center justify-center gap-2 rounded-full px-8 py-4 font-bold text-base sm:text-lg text-lucky-700 bg-white border-2 border-lucky-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all"
                        >
                            <span className="text-xl sm:text-2xl">🏆</span>
                            View Campaigns
                        </Link>
                    </div>

                    {/* How it Works */}
                    <div className="w-full max-w-4xl mb-14">
                        <h2 className="font-display text-2xl text-center text-gray-900 mb-8">How it Works</h2>
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-0 md:gap-0 relative">
                            {/* Connecting line (desktop) */}
                            <div className="hidden md:block absolute top-12 left-[16.667%] right-[16.667%] h-0.5 border-t-2 border-dashed border-lucky-300 z-0" />

                            <StepCard step={1} icon="📱" title="Sign Up & Choose a Plan" description="Create your free account and pick a plan that suits you." />
                            <StepCard step={2} icon="🎫" title="Redeem Coupons" description="Use exclusive discount coupons at partner stores and pay less." />
                            <StepCard step={3} icon="🏅" title="Earn Stamps & Win" description="Collect stamps with every purchase and win campaign rewards!" />
                        </div>
                    </div>

                    {/* Feature cards */}
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6 sm:gap-8 max-w-5xl w-full px-4 sm:px-0">
                        <FeatureTicket
                            emoji="🎫"
                            title="Collect Stamps"
                            description="Every purchase earns you stamps. Stack them up for bigger campaign rewards!"
                            color="lucky"
                        />
                        <FeatureTicket
                            emoji="🎁"
                            title="Exclusive Discounts"
                            description="Unlock coupons with real savings at your favourite local stores."
                            color="ticket"
                        />
                        <FeatureTicket
                            emoji="🏅"
                            title="Win Campaigns"
                            description="Your stamps fuel campaigns. Reach the target and win the prize!"
                            color="prize"
                        />
                    </div>

                    {/* Trust badges */}
                    <div className="mt-14 flex flex-wrap items-center justify-center gap-4 sm:gap-6 text-sm text-gray-500 px-4">
                        <TrustBadge label="Trusted by merchants" />
                        <TrustBadge label="Secure payments" />
                        <TrustBadge label="Instant rewards" />
                    </div>
                </main>

                {/* Footer */}
                <footer className="relative z-10 text-center py-8 text-sm text-gray-400 border-t border-lucky-100">
                    <p>&copy; {new Date().getFullYear()} Kutoot. Scratch, Win, Repeat!</p>
                </footer>
            </div>
        </>
    );
}

function StepCard({ step, icon, title, description }) {
    return (
        <div className="flex flex-col items-center text-center relative z-10 px-4 py-2">
            <div className="w-24 h-24 rounded-full bg-white border-4 border-dashed border-lucky-300 flex items-center justify-center mb-4 shadow-lg relative">
                <span className="text-3xl">{icon}</span>
                <span className="absolute -top-2 -right-2 w-7 h-7 rounded-full lucky-gradient text-white text-xs font-bold flex items-center justify-center shadow-md">
                    {step}
                </span>
            </div>
            <h3 className="font-display text-base text-gray-900 mb-1">{title}</h3>
            <p className="text-sm text-gray-500 max-w-[200px]">{description}</p>
        </div>
    );
}

function FeatureTicket({ emoji, title, description, color }) {
    const styles = {
        lucky: {
            border: 'border-lucky-300 hover:border-lucky-400',
            bg: 'from-lucky-50 to-lucky-100/50',
            text: 'text-lucky-700',
            iconBg: 'bg-lucky-100',
        },
        ticket: {
            border: 'border-ticket-300 hover:border-ticket-400',
            bg: 'from-ticket-50 to-ticket-100/50',
            text: 'text-ticket-700',
            iconBg: 'bg-ticket-100',
        },
        prize: {
            border: 'border-prize-300 hover:border-prize-400',
            bg: 'from-prize-50 to-prize-100/50',
            text: 'text-prize-700',
            iconBg: 'bg-prize-100',
        },
    };

    const s = styles[color] || styles.lucky;

    return (
        <div className={`coupon-card group hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 hover:scale-[1.02] ${s.border}`}>
            <div className={`bg-gradient-to-b ${s.bg} p-6 sm:p-8 text-center`}>
                <div className={`w-16 h-16 ${s.iconBg} rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform duration-300`}>
                    <span className="text-3xl">{emoji}</span>
                </div>
                <h3 className={`font-display text-lg sm:text-xl mb-2 ${s.text}`}>{title}</h3>
                <p className="text-gray-600 text-sm leading-relaxed">{description}</p>
            </div>
            {/* Ticket perforation */}
            <div className="flex justify-center gap-2 py-2 bg-gradient-to-r from-transparent via-gray-100 to-transparent">
                {[...Array(8)].map((_, i) => (
                    <div key={i} className="w-2 h-2 rounded-full bg-gray-200" />
                ))}
            </div>
        </div>
    );
}

function TrustBadge({ label }) {
    return (
        <div className="flex items-center gap-2 bg-white/80 px-4 py-2 rounded-full border border-lucky-100 shadow-sm">
            <span className="text-green-500 text-base">✓</span>
            <span>{label}</span>
        </div>
    );
}
