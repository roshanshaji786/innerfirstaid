import mongoose from 'mongoose';
import type { Mongoose } from 'mongoose';

const cached: { conn: Mongoose | null; promise: Promise<Mongoose> | null } =
  global.mongoose ?? (global.mongoose = { conn: null, promise: null });

async function dbConnect() {
  const MONGODB_URI = process.env.MONGODB_URI;

  if (!MONGODB_URI) {
    throw new Error('Please define MONGODB_URI environment variable');
  }

  if (cached.conn) return cached.conn;

  if (!cached.promise) {
    const opts = { bufferCommands: false };
    cached.promise = mongoose.connect(MONGODB_URI, opts).then((m) => m);
  }

  cached.conn = await cached.promise;
  return cached.conn;
}

export default dbConnect;
