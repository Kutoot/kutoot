# S3 Public Bucket Setup

To make the S3 bucket publicly readable (accessible by all microservices without auth):

## 1. AWS Console – Disable Block Public Access

1. Go to **S3** → select your bucket (e.g. `kutoot-backend`)
2. **Permissions** tab → **Block public access**
3. Click **Edit** → uncheck **Block all public access** → Save

## 2. Add Bucket Policy

1. **Permissions** tab → **Bucket policy** → **Edit**
2. Paste this policy (replace `kutoot-backend` with your bucket name):

```json
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Sid": "PublicReadGetObject",
      "Effect": "Allow",
      "Principal": "*",
      "Action": "s3:GetObject",
      "Resource": "arn:aws:s3:::kutoot-backend/*"
    }
  ]
}
```

3. Save

## 3. Environment (.env)

Ensure these are set:

```
FILESYSTEM_DRIVER=s3
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=ap-south-1
AWS_BUCKET=kutoot-backend
```

Optional – custom public URL (e.g. CloudFront):

```
AWS_PUBLIC_URL=https://your-cdn.cloudfront.net
```

If not set, URLs use: `https://{bucket}.s3.{region}.amazonaws.com/{path}`

## 4. Clear config cache

```bash
php artisan config:clear
```

## Result

- Media URLs will be direct S3 URLs (e.g. `https://kutoot-backend.s3.ap-south-1.amazonaws.com/72/01KJS9EGD39ZMH2J710N2E4ZNW.mp4`)
- Any microservice or frontend can access them without auth
- The `/storage/{path}` Laravel route is no longer needed for public bucket
