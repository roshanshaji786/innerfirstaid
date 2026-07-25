import mongoose, { Schema, models, model } from 'mongoose';

const LeadSchema = new Schema({
  email: { type: String, required: true, unique: true, lowercase: true, trim: true },
  lang: { type: String, enum: ['en', 'sl'], default: 'en' },
  source: { type: String, default: 'landing_page' },
  createdAt: { type: Date, default: Date.now },
});

export default models.Lead || model('Lead', LeadSchema);
