import Pagination from '@/Components/Pagination';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, usePage } from '@inertiajs/react';

export default function Index(props) {
    console.log(props.profiles);
    return (
        <AuthenticatedLayout
            auth={props.auth}
            errors={props.errors}
            header={<h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Currently Streaming</h2>}
        >
            <Head title="Currently Streaming" />
            <div className={"max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8"}>

            {props.profiles.map((item, index) => {
                return (
                <div className="bg-gray-800 shadow-lg text-white rounded-lg mt-4">
                <div className="md:flex">
                    <div className="p-5">
                    <h3 className={"text-xl font-semibold"}>
                        <a href={item.url} target="_blank">
                            {item.name}
                        </a>
                    </h3>
                    <p className={`mt-2 ${item.isLive ? 'text-red-500' : 'text-gray-500'}`}>
                        {item.isLive ? 'Live' : 'Offline'}
                    </p>
                    </div>
                </div>
                </div>
                )
            })}

            </div>
        </AuthenticatedLayout>
    )
}
