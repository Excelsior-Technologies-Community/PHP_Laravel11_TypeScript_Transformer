declare namespace App.Models {
export type Post = {
id: number;
title: string;
body: string;
user_id: number;
incrementing: boolean;
preventsLazyLoading: boolean;
exists: boolean;
wasRecentlyCreated: boolean;
timestamps: boolean;
usesUniqueIds: boolean;
};
export type User = {
id: number;
name: string;
email: string;
password: string | null;
incrementing: boolean;
preventsLazyLoading: boolean;
exists: boolean;
wasRecentlyCreated: boolean;
timestamps: boolean;
usesUniqueIds: boolean;
};
}
