import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import AddStreamerForm from './partials/AddStreamerForm';


export default function Add(props) {

    return (
        <AuthenticatedLayout
            auth={props.auth}
            errors={props.errors}
            header={<h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Add Streamer</h2>}
        >
            <Head title="Add Streamer" />
    

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div className="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                        <AddStreamerForm {...props} />
                    </div>
                </div>
            </div>

        </AuthenticatedLayout>
    )
}
