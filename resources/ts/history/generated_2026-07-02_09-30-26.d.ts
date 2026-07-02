declare namespace App.Models {
  export type PostStatus = 'draft' | 'published' | 'archived';

  export type Post = {
    id?: number;
    title: string;  /** validation: required|string|max:255 */
    body: string;  /** validation: required|string */
    user_id: string;  /** validation: required|integer */
    status?: PostStatus;  /** validation: nullable */
    published_at?: string;
    meta?: Record<string, any>;
  };

  export type User = {
    id?: number;
  };

}
