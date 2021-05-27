import {useRoute} from '../../shared/router';
import {useCallback} from 'react';

export const useSaveUser = (username: string) => {
    const url = useRoute('pim_user_user_rest_post', {identifier: username});

    return useCallback(async (data: {profile: string}) => {
        const response = await fetch(
            url,
            {
                method: 'POST',
                headers: [['X-Requested-With', 'XMLHttpRequest']],
                body: JSON.stringify(data),
            }
        );

        return response.json();
    }, [url]);
};
