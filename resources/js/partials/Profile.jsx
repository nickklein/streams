import Dropdown from '../partials/Dropdown';
import { useState } from 'react';

const Profile = (props) => {
    const [isFavourited, setIsFavourite] = useState(props.favourite ?? false);
    const favourite = (id) => {
        alert("favourite"+id);
    }
    const manage = () => {
        alert("manage");
    }
    return (
        <div className="bg-gray-800 shadow-lg text-white rounded-lg mt-4">
            <div className="p-5 leading-none">
                <div className="flex gap-2">
                    {props.name ? (
                        <>
                            <h3 className={"text-xl font-semibold flex"}>
                                <a href={props.url} target="_blank">
                                    {props.name}
                                </a>
                                <Dropdown>
                                    <Dropdown.Trigger>
                                        <svg className="w-4 h-4 text-gray-500 mt-2 cursor-pointer" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </Dropdown.Trigger>
                                    <Dropdown.Content>
                                        <Dropdown.Ajax method={manage}>Manage</Dropdown.Ajax>
                                        <Dropdown.Ajax method={() => favourite(props.id)}>{isFavourited ? 'Unfavourite' : 'Favourite'}</Dropdown.Ajax>
                                    </Dropdown.Content>
                                </Dropdown>
                            </h3>
                            <p className={`mt-2 ${props.isLive ? 'text-red-500' : 'text-gray-500'}`}>
                                {props.isLive ? 'Live' : 'Offline'}
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
}

export default Profile 
