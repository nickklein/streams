import Pagination from '@/Components/Pagination';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, usePage } from '@inertiajs/react';
import { useState, useEffect } from 'react';

export default function Index(props) {
    const [profiles, setProfiles] = useState(props.profiles);

    function getProfiles() {
        profiles.forEach(profile => {
            axios.get(route('streams.get-profile', profile.id))
            .then(function(response) {
                setProfiles(prevProfiles => {
                    const updatedProfiles = prevProfiles.map(item => {
                        if (item.id === profile.id) {
                            return { ...item, ...response.data };
                        }
                        return item;
                    });

                    return updatedProfiles.sort((a, b) => {
                        if (!a.name || !b.name) {
                            console.warn("Missing name in one or more profiles:", a, b);
                        }
                        const nameA = a.name || "";
                        const nameB = b.name || "";
                        if (b.isLive !== a.isLive) {
                            return (b.isLive === true) - (a.isLive === true);
                        }

                        return nameA.localeCompare(nameB);
                    });
                });

            });
        });
    }

    useEffect(() => {
        getProfiles();
    }, []);

    return (
        <AuthenticatedLayout
            auth={props.auth}
            errors={props.errors}
            header={<h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Currently Streaming</h2>}
        >
            <Head title="Currently Streaming" />
            <div className={"max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8"}>

            {profiles && profiles.map((item, index) => {
                return (
                <div className="bg-gray-800 shadow-lg text-white rounded-lg mt-4">
                    <div className="p-5 leading-none">
                        <div className="flex gap-2">
                            {item.name ? (
                                <>
                                    <h3 className={"text-xl font-semibold"}>
                                        <a href={item.url} target="_blank">
                                            {item.name}
                                        </a>
                                    </h3>
                                    <p className={`mt-2 ${item.isLive ? 'text-red-500' : 'text-gray-500'}`}>
                                        {item.isLive ? 'Live' : 'Offline'}
                                    </p>
                                </>
                            ) : ( 
                                <>
                                    <div className="block bg-gray-900 rounded w-48 h-6"></div>
                                    <div className="block bg-gray-900 rounded w-12 h-6"></div>
                                </>
                            )}
                        </div>
                    </div>
                </div>
                )
            })}

            </div>
        </AuthenticatedLayout>
    )
}
