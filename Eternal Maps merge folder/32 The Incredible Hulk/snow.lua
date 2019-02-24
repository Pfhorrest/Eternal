---Embers script used for End Times in Imperium by Windbreaker. Lua by Wrkncacnter (mostly).

CollectionsUsed = { 24 }

precipitation_type = "bob pieces"
precipitation_count = 512
precipitation_phase = 1
precipitation_gravity = .03
precipitation_wind = .01

scenery_cleared = false

function build_pool()
	Level._pool = {}
	
	local count = 0
	for i = 1, precipitation_count do
		 local x, y, z, p = uniform.xyz_in_triangle_list(Level._triangles)
		 s = Scenery.new(x, y, z, p, precipitation_type)
		 if s then
			  count = count + 1
			  table.insert(Level._pool, s)
		 end
	end
	precipitation_count = count
end

Triggers = {}

function Triggers.got_item(type, player)

	if type == "pistol" then
		if player.items["pistol"] > 2 then
			player.items["pistol"] = 2
			player.items["pistol ammo"] = player.items["pistol ammo"] + 1
		end

	elseif type == "fusion pistol" then
		if player.items["fusion pistol"] > 1 then
			player.items["fusion pistol"] = 1
			player.items["pistol ammo"] = player.items["pistol ammo"] + 1
		end

	elseif type == "assault rifle" then
		if player.items["assault rifle"] > 1 then
			player.items["assault rifle"] = 1
			player.items["assault rifle ammo"] = player.items["assault rifle ammo"] + 1
		end

	elseif type == "missile launcher" then
		if player.items["missile launcher"] > 1 then
			player.items["missile launcher"] = 1
			player.items["missile launcher ammo"] = player.items["missile launcher ammo"] + 1
			player.items["assault rifle grenades"] = player.items["assault rifle grenades"] + 1
		end

	elseif type == "alien weapon" then
		if player.items["alien weapon"] > 1 then
			player.items["alien weapon"] = 1
			player.items["shotgun ammo"] = player.items["shotgun ammo"] + 1
		end

	elseif type == "flamethrower" then
		if player.items["flamethrower"] > 1 then
			player.items["flamethrower"] = 1
			player.items["flamethrower ammo"] = player.items["flamethrower ammo"] + 1
		end

	elseif type == "shotgun" then
		if player.items["shotgun"] > 2 then
			player.items["shotgun"] = 2
			player.items["smg ammo"] = player.items["smg ammo"] + 1
		end

	elseif type == "smg" then
		if player.items["smg"] > 1 then
			player.items["smg"] = 1
			player.items["smg ammo"] = player.items["smg ammo"] + 1
		end

	elseif type == "assault rifle ammo" then
		if player.items["assault rifle"] < 1 then
			player.items["assault rifle ammo"] = player.items["assault rifle ammo"] - 1
			player.items["assault rifle"] = 1
		end

	elseif type == "assault rifle grenades" then
		if player.items["missile launcher"] < 1 then
			player.items["assault rifle grenades"] = player.items["assault rifle grenades"] - 1
			player.items["missile launcher"] = 1
		end

	elseif type == "missile launcher ammo" then
		if player.items["missile launcher"] < 1 then
			player.items["missile launcher ammo"] = player.items["missile launcher ammo"] - 1
			player.items["missile launcher"] = 1
		else
			player.items["assault rifle grenades"] = player.items["assault rifle grenades"] + 1
		end

	elseif type == "shotgun ammo" then
		if player.items["alien weapon"] < 1 then
			player.items["shotgun ammo"] = player.items["shotgun ammo"] - 1
			player.items["alien weapon"] = 1
		end

	elseif type == "flamethrower ammo" then
		if player.items["flamethrower"] < 1 then
			player.items["flamethrower ammo"] = player.items["flamethrower ammo"] - 1
			player.items["flamethrower"] = 1
		end

	elseif type == "fusion pistol ammo" then
		player.items["fusion pistol ammo"] = 0
		player.items["pistol ammo"] = player.items["pistol ammo"] + 1
	
	elseif type == "alien weapon ammo" then
		player.items["alien weapon ammo"] = 0
		if player.items["flamethrower ammo"] > player.items["shotgun ammo"] then
			player.items["shotgun ammo"] = player.items["shotgun ammo"] + 1
		else
			player.items["flamethrower ammo"] = player.items["flamethrower ammo"] + 1
		end
	end
end

-- function Triggers.projectile_created(projectile)
--     projectile._original_owner = projectile.owner
-- end

function Triggers.player_damaged(victim, aggressor_player, aggressor_monster, damage_type, damage_amount, projectile)
    if projectile and projectile.type == ( "shotgun bullet" or "smg bullet" ) and victim.weapons.current.type == ( "smg" or "shotgun" ) then
        victim.life = victim.life + damage_amount
    end
end

function Triggers.init(restoring)
	local polygon_list = {}
	for p in Polygons() do
		if p.ceiling.transfer_mode == "landscape" then
			table.insert(polygon_list, p)
		end
	end
	Level._triangles = uniform.build_triangle_list(polygon_list)
	if #polygon_list == 0 then
		precipitation_count = 0
	else
		local total_precipitation_area = 0
		for _, t in pairs(Level._triangles) do
			total_precipitation_area = total_precipitation_area + t.area
		end
		precipitation_count = total_precipitation_area * 2
		if precipitation_count > 700 then
			precipitation_count = 700
		end
	end
	
	if restoring then
		Level._pool = {}
		local count = 0
		for s in Scenery() do
			if s.type == precipitation_type then
				count = count + 1
				table.insert(Level._pool, s)
			end
		end
		precipitation_count = count
	else
		build_pool()
	end
end

function Triggers.idle()
   if scenery_cleared == true then
	build_pool()
	scenery_cleared = false
   end
   local pool = Level._pool
   local position = pool[1].position
   local phase = precipitation_phase
   local gravity = phase * precipitation_gravity
   local wind = phase * precipitation_wind
   local phase_match = Game.ticks % phase
   for i = 1,precipitation_count do
      if i % phase == phase_match then
	   	local e = pool[i]
			if e then
				position(e, e.x - wind, e.y - wind, e.z - gravity, e.polygon)
	 			if e.z < e.polygon.floor.height then
	    			local x, y, p = uniform.xy_in_triangle_list(Level._triangles)
	    			e:position(x, y, p.ceiling.height, p)
	 			elseif e.z > e.polygon.ceiling.height then
				   local x, y, p = uniform.xy_in_triangle_list(Level._triangles)
				   e:position(x, y, p.floor.height, p)
				end
			end
      end
   end
end
