import { getApp, getApps, initializeApp } from 'firebase/app';
import { getAnalytics, isSupported } from 'firebase/analytics';

const firebaseConfig = {
    apiKey: 'AIzaSyCPLQ8h1lDbCwrkaHLBlcGTsVwQ7Z4esRA',
    authDomain: 'rgv-system.firebaseapp.com',
    projectId: 'rgv-system',
    storageBucket: 'rgv-system.firebasestorage.app',
    messagingSenderId: '686696003488',
    appId: '1:686696003488:web:9108bafc744a8f6c8056c5',
    measurementId: 'G-2RSNYYMD6T',
};

const firebaseApp = getApps().length ? getApp() : initializeApp(firebaseConfig);

if (typeof window !== 'undefined') {
    isSupported()
        .then((supported) => {
            if (supported) {
                getAnalytics(firebaseApp);
            }
        })
        .catch(() => {});
}

export default firebaseApp;