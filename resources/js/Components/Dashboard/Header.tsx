import { Link, usePage } from '@inertiajs/react';
import { Menu } from 'lucide-react';
import Dropdown from '@/Components/Dropdown';

export default function Header() {
    const { auth, locale, translations } = usePage().props as any;
    const user = auth.user;
    const tLang = translations.auth_ui.language_switcher;
    const tAuth = translations.auth_ui;
    const tNav = translations.profile.navigation;
    const nextLocale = locale === 'en' ? 'vi' : 'en';

    return (
        <header className="flex h-16 items-center justify-between border-b border-gray-200 bg-white px-4 sm:px-6 lg:px-8">
            <div className="flex items-center">
                <button className="mr-2 -ml-2 rounded-md p-2 text-gray-500 hover:text-gray-700 focus:ring-2 focus:ring-indigo-500 focus:outline-none md:hidden">
                    <Menu size={24} />
                </button>
            </div>

            <div className="flex items-center space-x-4">
                <Link
                    href={route('locale.update', { locale: nextLocale })}
                    method="post"
                    as="button"
                    className="text-sm font-medium text-gray-500 transition-colors hover:text-gray-900"
                >
                    {tLang[nextLocale]}
                </Link>

                <div className="relative ms-3">
                    <Dropdown>
                        <Dropdown.Trigger>
                            <span className="inline-flex rounded-md">
                                <button
                                    type="button"
                                    className="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm leading-4 font-medium text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none"
                                >
                                    {user.name}
                                    <svg
                                        className="ms-2 -me-0.5 h-4 w-4"
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                    >
                                        <path
                                            fillRule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clipRule="evenodd"
                                        />
                                    </svg>
                                </button>
                            </span>
                        </Dropdown.Trigger>

                        <Dropdown.Content>
                            <Dropdown.Link href={route('profile.edit')}>
                                {tNav.profile}
                            </Dropdown.Link>
                            <Dropdown.Link
                                href={route('logout')}
                                method="post"
                                as="button"
                            >
                                {tAuth.logout}
                            </Dropdown.Link>
                        </Dropdown.Content>
                    </Dropdown>
                </div>
            </div>
        </header>
    );
}
