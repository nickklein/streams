import React from 'react'
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import SelectOptions from '@/Components/SelectOptions';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { useForm } from '@inertiajs/react';
import { Transition } from '@headlessui/react';


function AddStreamerForm(props) {
    const { data, setData, errors, post, reset, processing, recentlySuccessful } = useForm({
        platform: props.platform || '',
        name: props.name || '',
        channel_id: props.channel_id || '',
        channel_url: props.channel_url || '',
    });

    function onSubmit(event) {
        event.preventDefault();
        post(route('streams.store'), {
            preserveScroll: true,
            onError: () => {
                if (errors.name) {
                    reset();
                }
            },
        });
    }

    return (
        <>
            <section className={`space-y-6`}>
                <header>
                    <h2 className="text-lg font-medium text-gray-900 dark:text-gray-100">Add Streamer</h2>

                    <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        
                    </p>
                </header>
            </section>

            <form onSubmit={onSubmit} className="mt-6 space-y-6">

                <div>
                    <InputLabel for="platform" value="Platform" />
                    <SelectOptions
                        options={[
                        {
                            value: 0,
                            label: "Select an option",
                        },
                        {
                            value: "kick",
                            label: "Kick",
                        },
                        {
                            value: "twitch",
                            label: "Twitch",
                        },
                        {
                            value: "youtube",
                            label: "Youtube"
                        }, {
                            value: "kick",
                            label: "Kick"
                        }]}
                        onChange={(event) => setData('platform', event.target.value)}
                    />

                    <InputError message={errors.platform} className="mt-2" />
                </div>
                <div>
                    <InputLabel for="name" value="Name" />
                    <TextInput 
                        handleChange={(event) => setData('name', event.target.value)} 
                        value={data.name} 
                    /> 

                    <InputError message={errors.name} className="mt-2" />
                </div>
                <div>
                    <InputLabel for="channel_id" value="Channel ID" />
                    <TextInput 
                        handleChange={(event) => setData('channel_id', event.target.value)} 
                        value={data.channel_id}
                    /> 

                    <InputError message={errors.channel_id} className="mt-2" />
                </div>
                <div>
                    <InputLabel for="channel_url" value="Channel URL" />
                    <TextInput 
                        handleChange={(event) => setData('channel_url', event.target.value)} 
                        value={data.channel_url}
                    /> 

                    <InputError message={errors.channel_url} className="mt-2" />
                </div>
                <div className="flex items-center gap-4">
                    <PrimaryButton processing={processing}>Save</PrimaryButton>

                    <Transition
                        show={recentlySuccessful}
                        enterFrom="opacity-0"
                        leaveTo="opacity-0"
                        className="transition ease-in-out"
                    >
                        <p className="text-sm text-gray-600 dark:text-gray-400">Saved.</p>
                    </Transition>
                </div>

            </form>

        </>
    )
}

export default AddStreamerForm 
